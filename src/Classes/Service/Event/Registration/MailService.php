<?php
namespace BIT\EMS\Service\Event\Registration;

use BIT\EMS\Domain\Model\Event\EventMetaBox;
use BIT\EMS\Domain\Model\EventRegistration;
use BIT\EMS\Domain\Repository\EventRepository;
use BIT\EMS\Exception\Event\SendRegistrationMailFailedException;
use BIT\EMS\Settings\Settings;
use BIT\EMS\Settings\Tab\EventManagerMailTab;
use BIT\EMS\Settings\Tab\ParticipantMailTab;
use Fum_Mail;

/**
 * @author Christoph Bessei
 */
class MailService
{
    const MAIL_TYPE_REGISTRATION = 1;
    const MAIL_TYPE_CANCEL_REGISTRATION = 2;

    /**
     * @var \BIT\EMS\Domain\Repository\EventRepository
     */
    protected $eventRepository;

    public function __construct()
    {
        $this->eventRepository = new EventRepository();
    }

    /**
     * Notify leader and participant about a registration
     * @param EventRegistration $registration
     * @throws \Exception
     */
    public function sendRegisterMail(EventRegistration $registration)
    {
        $this->sendMail($registration, static::MAIL_TYPE_REGISTRATION);
    }

    /**
     * Notify leader and participant about a cancelled registration
     * @param EventRegistration $registration
     * @throws \Exception
     */
    public function sendCancelMail(EventRegistration $registration)
    {
        $this->sendMail($registration, static::MAIL_TYPE_CANCEL_REGISTRATION);
    }

    /**
     * @param EventRegistration $registration
     * @param $mail_type
     * @throws \BIT\EMS\Exception\Event\SendRegistrationMailFailedException
     * @throws \Exception
     */
    protected function sendMail(EventRegistration $registration, $mail_type)
    {
        // Common variables
        $eventId = $registration->getEventId();
        $event = \Ems_Event::get_event($eventId);
        $user = get_userdata($registration->getUserId());

        $leader_email = $this->eventRepository->findEventManagerMail($eventId);

        $send_leader_email = 1 == get_post_meta(
                $registration->getEventId(),
                EventMetaBox::INFORM_EVENT_MANAGER_ABOUT_NEW_PARTICIPANTS,
                true
            );

        switch ($mail_type) {
            case static::MAIL_TYPE_REGISTRATION:
                // Send event registration confirmation to participant
                $mail = $this->loadParticipantRegistrationSuccessfulMail($user, $event);
                list($subject, $message) = $mail;

                if ($send_leader_email && false !== $leader_email) {
                    // Send notification about new registration to event manager
                    $eventManagerMail = $this->loadEventManagerRegistrationSuccessfulMail($user, $event);
                    list($eventManagerSubject, $eventManagerMessage) = $eventManagerMail;
                    Fum_Mail::sendHtmlMail($leader_email, $eventManagerSubject, $eventManagerMessage);
                }
                break;
            case static::MAIL_TYPE_CANCEL_REGISTRATION:
                // Send cancel registration confirmation to participant
                $mail = $this->loadParticipantCancelRegistrationMail($user, $event);
                list($subject, $message) = $mail;

                if ($send_leader_email && false !== $leader_email) {
                    // Inform event manager about cancelled registration
                    $eventManagerMail = $this->loadEventManagerCancelRegistrationMail($user, $event);
                    list($eventManagerSubject, $eventManagerMessage) = $eventManagerMail;
                    Fum_Mail::sendHtmlMail($leader_email, $eventManagerSubject, $eventManagerMessage);
                }
                break;
        }

        try {
            // Send mail to participant
            if (!empty($subject) && !empty($message)) {
                Fum_Mail::sendHtmlMail($user->user_email, $subject, $message, $leader_email);
            }

            // Send mail to event leader
            if ($send_leader_email && false !== $leader_email && !empty($leader_subject) && !empty($leader_message)) {
                Fum_Mail::sendHtmlMail($leader_email, $leader_subject, $leader_message);
            }
        } catch (\Exception $e) {
            $msg = "Konnte Bestätitungsmail nicht versenden. Bitte versuche es später nochmal.";
            echo $msg;
            throw new SendRegistrationMailFailedException($msg, 1510467545, $e);
        }
    }

    protected function loadMailFromSettings($section, $option): string
    {
        $subject = Settings::get($option, $section);
        $subject = wpautop($subject);
        return $subject;
    }

    protected function loadMailPartFromSettingsAndReplaceMarker($section, $option, $user, $event)
    {
        $part = $this->loadMailFromSettings($section, $option);
        return $this->replaceMarkers($part, $user, $event);
    }

    protected function loadParticipantRegistrationSuccessfulMail($user, \Ems_Event $event)
    {
        // Load event specific mail
        $useCustomParticipantMail = get_post_meta($event->ID, EventMetaBox::USE_CUSTOM_PARTICIPANT_MAIL, true);
        $participantMailSubject = trim(get_post_meta($event->ID, EventMetaBox::PARTICIPANT_MAIL_SUBJECT, true));
        $participantMailBody = trim(get_post_meta($event->ID, EventMetaBox::PARTICIPANT_MAIL_BODY, true));

        if (!empty($useCustomParticipantMail) && !empty($participantMailSubject) && !empty($participantMailBody)) {
            $subject = $this->replaceMarkers($participantMailSubject, $user, $event);
            $message = $this->replaceMarkers($participantMailBody, $user, $event);
        } else {
            $subject = $this->loadMailPartFromSettingsAndReplaceMarker(
                ParticipantMailTab::class,
                ParticipantMailTab::EVENT_REGISTRATION_SUCCESSFUL_SUBJECT,
                $user,
                $event
            );

            $message = $this->loadMailPartFromSettingsAndReplaceMarker(
                ParticipantMailTab::class,
                ParticipantMailTab::EVENT_REGISTRATION_SUCCESSFUL_BODY,
                $user,
                $event
            );
        }

        return [$subject, $message];
    }

    protected function loadParticipantCancelRegistrationMail($user, \Ems_Event $event)
    {
        $subject = $this->loadMailPartFromSettingsAndReplaceMarker(
            ParticipantMailTab::class,
            ParticipantMailTab::EVENT_CANCEL_REGISTRATION_SUBJECT,
            $user,
            $event
        );

        $message = $this->loadMailPartFromSettingsAndReplaceMarker(
            ParticipantMailTab::class,
            ParticipantMailTab::EVENT_CANCEL_REGISTRATION_BODY,
            $user,
            $event
        );

        return [$subject, $message];
    }

    protected function loadEventManagerRegistrationSuccessfulMail($user, \Ems_Event $event)
    {
        $subject = $this->loadMailPartFromSettingsAndReplaceMarker(
            EventManagerMailTab::class,
            EventManagerMailTab::EVENT_REGISTRATION_SUCCESSFUL_SUBJECT,
            $user,
            $event
        );

        $message = $this->loadMailPartFromSettingsAndReplaceMarker(
            EventManagerMailTab::class,
            EventManagerMailTab::EVENT_REGISTRATION_SUCCESSFUL_BODY,
            $user,
            $event
        );

        return [$subject, $message];
    }

    protected function loadEventManagerCancelRegistrationMail($user, \Ems_Event $event)
    {
        $subject = $this->loadMailPartFromSettingsAndReplaceMarker(
            EventManagerMailTab::class,
            EventManagerMailTab::EVENT_CANCEL_REGISTRATION_SUBJECT,
            $user,
            $event
        );

        $message = $this->loadMailPartFromSettingsAndReplaceMarker(
            EventManagerMailTab::class,
            EventManagerMailTab::EVENT_CANCEL_REGISTRATION_BODY,
            $user,
            $event
        );

        return [$subject, $message];
    }


    protected function replaceMarkers(string $text, \WP_User $user, \Ems_Event $event)
    {
        $eventTitle = htmlspecialchars_decode($event->get_post()->post_title);
        $eventId = $event->getID();

        return str_ireplace(
            [
                '###user_firstname###',
                '###user_lastname###',
                '###event_title###',
                '###event_participant_list_link###',
            ],
            [
                $user->user_firstname,
                $user->user_lastname,
                $eventTitle,
                get_permalink(get_option('ems_partcipant_list_page')) . '?select_event=ID_' . $eventId,
            ],
            $text
        );
    }
}

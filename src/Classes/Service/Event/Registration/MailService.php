<?php
namespace BIT\EMS\Service\Event\Registration;

use BIT\EMS\Domain\Model\EventRegistration;
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
        $event_title = htmlspecialchars_decode(get_post($registration->getEventId())->post_title);
        $eventId = $registration->getEventId();
        $user = get_userdata($registration->getUserId());

        $leader_id = get_post_meta($registration->getEventId(), 'ems_event_leader', true);
        $leader = get_userdata($leader_id);

        if (false === $leader) {
            $leader_email = get_post_meta($registration->getEventId(), 'ems_event_leader_mail', true);
        } else {
            $leader_email = $leader->user_email;
        }

        $send_leader_email = 1 == get_post_meta($registration->getEventId(), 'ems_inform_via_mail', true);

        switch ($mail_type) {
            case static::MAIL_TYPE_REGISTRATION:
                $subject = $this->loadMailFromSettings(
                    ParticipantMailTab::class,
                    ParticipantMailTab::EVENT_REGISTRATION_SUCCESSFUL_SUBJECT
                );
                $subject = $this->replaceMarkers($subject, $user, $event_title, $eventId);

                $message = $this->loadMailFromSettings(
                    ParticipantMailTab::class,
                    ParticipantMailTab::EVENT_REGISTRATION_SUCCESSFUL_BODY
                );
                $message = $this->replaceMarkers($message, $user, $event_title, $eventId);

                if ($send_leader_email && false !== $leader_email) {
                    $eventManagerSubject = $this->loadMailFromSettings(
                        EventManagerMailTab::class,
                        EventManagerMailTab::EVENT_REGISTRATION_SUCCESSFUL_SUBJECT
                    );
                    $eventManagerSubject = $this->replaceMarkers($eventManagerSubject, $user, $event_title, $eventId);

                    $eventManagerMessage = $this->loadMailFromSettings(
                        EventManagerMailTab::class,
                        EventManagerMailTab::EVENT_REGISTRATION_SUCCESSFUL_BODY
                    );
                    $eventManagerMessage = $this->replaceMarkers($eventManagerMessage, $user, $event_title, $eventId);

                    Fum_Mail::sendMail($leader_email, $eventManagerSubject, $eventManagerMessage);
                }
                break;
            case static::MAIL_TYPE_CANCEL_REGISTRATION:
                $subject = $this->loadMailFromSettings(
                    ParticipantMailTab::class,
                    ParticipantMailTab::EVENT_CANCEL_REGISTRATION_SUBJECT
                );
                $subject = $this->replaceMarkers($subject, $user, $event_title, $eventId);

                $message = $this->loadMailFromSettings(
                    ParticipantMailTab::class,
                    ParticipantMailTab::EVENT_CANCEL_REGISTRATION_BODY
                );
                $message = $this->replaceMarkers($message, $user, $event_title, $eventId);

                if ($send_leader_email && false !== $leader_email) {
                    $eventManagerSubject = $this->loadMailFromSettings(
                        EventManagerMailTab::class,
                        EventManagerMailTab::EVENT_CANCEL_REGISTRATION_SUBJECT
                    );
                    $eventManagerSubject = $this->replaceMarkers($eventManagerSubject, $user, $event_title, $eventId);

                    $eventManagerMessage = $this->loadMailFromSettings(
                        EventManagerMailTab::class,
                        EventManagerMailTab::EVENT_CANCEL_REGISTRATION_BODY
                    );
                    $eventManagerMessage = $this->replaceMarkers($eventManagerMessage, $user, $event_title, $eventId);

                    Fum_Mail::sendMail($leader_email, $eventManagerSubject, $eventManagerMessage);
                }
                break;
        }

        try {
            // Send mail to participant
            if (!empty($subject) && !empty($message)) {
                Fum_Mail::sendMail($user->user_email, $subject, $message, $leader_email);
            }

            // Send mail to event leader
            if ($send_leader_email && false !== $leader_email && !empty($leader_subject) && !empty($leader_message)) {
                Fum_Mail::sendMail($leader_email, $leader_subject, $leader_message);
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

    protected function replaceMarkers(string $text, \WP_User $user, string $event_title, int $eventId)
    {
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
                $event_title,
                get_permalink(get_option('ems_partcipant_list_page')) . '?select_event=ID_' . $eventId,
            ],
            $text
        );
    }
}

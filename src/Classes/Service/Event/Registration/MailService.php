<?php
namespace BIT\EMS\Service\Event\Registration;

use BIT\EMS\Exception\Event\SendRegistrationMailFailedException;
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
     * @param \Ems_Event_Registration $registration
     * @throws \Exception
     */
    public function sendRegisterMail(\Ems_Event_Registration $registration)
    {
        $this->sendMail($registration, static::MAIL_TYPE_REGISTRATION);
    }

    /**
     * Notify leader and participant about a cancelled registration
     * @param \Ems_Event_Registration $registration
     * @throws \Exception
     */
    public function sendCancelMail(\Ems_Event_Registration $registration)
    {
        $this->sendMail($registration, static::MAIL_TYPE_CANCEL_REGISTRATION);
    }

    /**
     * @param \Ems_Event_Registration $registration
     * @param $mail_type
     * @throws \BIT\EMS\Exception\Event\SendRegistrationMailFailedException
     * @throws \Exception
     */
    protected function sendMail(\Ems_Event_Registration $registration, $mail_type)
    {
        // Common variables
        $event_title = htmlspecialchars_decode(get_post($registration->get_event_post_id())->post_title);
        $user = get_userdata($registration->get_user_id());

        $leader_id = get_post_meta($registration->get_event_post_id(), 'ems_event_leader', true);
        $leader = get_userdata($leader_id);

        if (false === $leader) {
            $leader_email = get_post_meta($registration->get_event_post_id(), 'ems_event_leader_mail', true);
        } else {
            $leader_email = $leader->user_email;
        }

        $send_leader_email = 1 == get_post_meta($registration->get_event_post_id(), 'ems_inform_via_mail', true);

        switch ($mail_type) {
            case static::MAIL_TYPE_REGISTRATION:
                $subject = 'Erfolgreich für "' . $event_title . '" registriert';
                $message =
                    'Liebe/r ' . $user->user_firstname . "," . PHP_EOL .
                    'du hast dich erfolgreich für das Event "' . $event_title . '" registriert.' . PHP_EOL .
                    'Du bekommst spätestens 14 Tage vor dem Event weitere Informationen vom Eventleiter zugeschickt.' . PHP_EOL .
                    'Viele Grüße,' . "\n" .
                    'Das DHV-Jugendteam';

                if ($send_leader_email && false !== $leader_email) {
                    $leader_subject = 'Es gibt eine neue Anmeldung für das "' . $event_title . '" Event';
                    $leader_message = $user->user_firstname . ' ' . $user->lastname . ' hat sich für dein Event "' . $event_title . '" angemeldet.' . PHP_EOL;
                    $leader_message .= 'Du kannst die Details zur Anmeldung auf ' . get_permalink(
                            get_option('ems_partcipant_list_page')
                        ) . '?select_event=ID_' . $registration->get_event_post_id() . ' einsehen';
                    // TODO Remove duplicate e-mail to leader and participant (was needed as backup)
                    Fum_Mail::sendMail($leader_email, $subject, $message);
                }
                break;
            case static::MAIL_TYPE_CANCEL_REGISTRATION:
                $subject = 'Erfolgreich von "' . $event_title . '" abgemeldet';
                $message =
                    'Liebe/r ' . $user->user_firstname . "." . PHP_EOL .
                    'Schade, dass du nicht mehr auf das Event "' . $event_title . '" möchtest.' . PHP_EOL .
                    'Wir würden uns freuen wenn wir dich auf einem anderen Event sehen würden.' . PHP_EOL .
                    'Vielleicht schaust du nochmal auf www.dhv-jugend.de/events/ vorbei und schaust dir unseren andere Events an?' . PHP_EOL .
                    'Viele Grüße,' . PHP_EOL .
                    'Das DHV-Jugendteam';

                if ($send_leader_email && false !== $leader_email) {
                    $leader_subject = 'Es gibt eine Abmeldung vom "' . $event_title . '" Event';
                    $leader_message = $user->user_firstname . ' ' . $user->lastname . ' hat sich von deinem Event "' . $event_title . '" abgemeldet.' . "\n";
                    $leader_message .= 'Du kannst die Details zur Abmeldung auf ' . get_permalink(
                            get_option('ems_partcipant_list_page')
                        ) . '?select_event=ID_' . $registration->get_event_post_id() . ' einsehen';
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
}

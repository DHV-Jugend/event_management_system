<?php

/**
 * @author Christoph Bessei
 * @version
 */
class Ems_Event_Registration extends Ems_Log
{
    const MAIL_TYPE_REGISTRATION = 1;
    const MAIL_TYPE_DELETE_REGISTRATION = 2;


    protected static $additionalLogFields = [
        'event' => 'int(11)',
        'user' => 'int(11)'
    ];

    protected static $option_name = 'ems_event_registration';

    private $event_post_id;
    private $user_id;
    /**
     * @var $data
     * Array of fields which belongs to the registration. Could be used for event specific information for the participants list
     */
    private $data;

    public function __construct($event_post_id, $user_id, $data = array())
    {
        $this->event_post_id = $event_post_id;
        $this->user_id = $user_id;
        $this->data = $data;
    }

    /**
     * @return mixed
     */
    public function get_event_post_id()
    {
        return $this->event_post_id;
    }

    /**
     * @return mixed
     */
    public function get_user_id()
    {
        return $this->user_id;
    }

    /**
     * @param array $data
     */
    public function set_data($data)
    {
        $this->data = $data;
    }

    /**
     * @return array
     */
    public function get_data()
    {
        return $this->data;
    }

    public function equals(self $otherObject)
    {
        if ($otherObject->get_event_post_id() == $this->get_event_post_id() && $otherObject->get_user_id() == $this->get_user_id()) {
            return true;
        }
        return false;
    }

    /**
     * @param \Ems_Event_Registration $registration
     * @throws \Exception
     */
    public static function add_event_registration(Ems_Event_Registration $registration)
    {
        $registrations = self::get_event_registrations();
        if (self::is_already_registered($registration)) {
            throw new Exception("User is already registered for this event");
        }
        $registrations[] = $registration;

        if (update_option(self::$option_name, $registrations)) {
            static::logEventRegistration('Added event registration.', $registration);

            static::send_registration_mail($registration, static::MAIL_TYPE_REGISTRATION);
            static::logEventRegistration('Sent add event registration mail.', $registration);
        } else {
            static::logEventRegistration("Couldn't add event registration.", $registration);
        }
    }


    /**
     * @param \Ems_Event_Registration $registration
     */
    public static function delete_event_registration(Ems_Event_Registration $registration)
    {
        $registrations = self::get_event_registrations();
        foreach ($registrations as $key => $cur_registration) {
            if ($registration->equals($cur_registration)) {
                unset($registrations[$key]);
                break;
            }
        }

        if (update_option(self::$option_name, $registrations)) {
            static::logEventRegistration('Deleted event registration.', $registration);

            static::logEventRegistration('Sent delete event registration mail.', $registration);
            static::send_registration_mail($registration, static::MAIL_TYPE_DELETE_REGISTRATION);
        } else {
            static::logEventRegistration("Couldn't delete event registration.", $registration);
        }

    }

    /**
     * Notify leader and participant about registration / delete registration
     * @param \Ems_Event_Registration $registration
     * @param $mail_type
     * @throws \Exception
     */
    protected static function send_registration_mail(Ems_Event_Registration $registration, $mail_type)
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
                    $leader_message .= 'Du kannst die Details zur Anmeldung auf ' . get_permalink(get_option('ems_partcipant_list_page')) . '?select_event=ID_' . $registration->get_event_post_id() . ' einsehen';
                    // TODO Remove duplicate e-mail to leader and participant (was needed as backup)
                    Fum_Mail::sendMail($leader_email, $subject, $message);
                }
                break;
            case static::MAIL_TYPE_DELETE_REGISTRATION:
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
                    $leader_message .= 'Du kannst die Details zur Abmeldung auf ' . get_permalink(get_option('ems_partcipant_list_page')) . '?select_event=ID_' . $registration->get_event_post_id() . ' einsehen';
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
        } catch (Exception $e) {
            echo "Konnte Bestätitungsmail nicht versenden. Bitte versuche es später nochmal.";
            throw $e;
        }
    }

    /**
     * @return Ems_Event_Registration[]
     */
    private static function get_event_registrations()
    {
        $registrations = get_option(self::$option_name);
        if (is_array($registrations)) {
            return $registrations;
        }
        return array();
    }

    /**
     * @param $event_post_id
     *
     * @return Ems_Event_Registration[]
     */
    public static function get_registrations_of_event($event_post_id)
    {
        $registrations = self::get_event_registrations();
        $event_registrations = array();
        foreach ($registrations as $registration) {
            if ($registration->get_event_post_id() == $event_post_id) {
                $event_registrations[] = $registration;
            }
        }
        return $event_registrations;
    }

    /**
     * @param $user_id
     *
     * @return Ems_Event_Registration[]
     */
    public static function get_registrations_of_user($user_id)
    {
        $registrations = self::get_event_registrations();
        $event_registrations = array();
        foreach ($registrations as $registration) {
            if ($registration->get_user_id() == $user_id) {
                $event_registrations[] = $registration;
            }
        }
        return $event_registrations;
    }

    /**
     * @param $event_id
     * @param $user_id
     * @return \Ems_Event_Registration|null
     */
    public static function get_registration($event_id, $user_id)
    {
        $registrations = self::get_event_registrations();
        foreach ($registrations as $registration) {
            if ($registration->get_user_id() == $user_id && $event_id == $registration->get_event_post_id()) {
                return $registration;
            }
        }
        return null;
    }

    public static function is_already_registered(Ems_Event_Registration $registration)
    {
        $registrations = self::get_event_registrations();
        $used = false;
        foreach ($registrations as $cur_registration) {
            if ($registration->get_event_post_id() == $cur_registration->get_event_post_id() && $registration->get_user_id() == $cur_registration->get_user_id()) {
                $used = true;
                break;
            }
        }
        return $used;
    }

    /**
     * @param $msg
     * @param \Ems_Event_Registration $registration
     */
    public static function logEventRegistration($msg, Ems_Event_Registration $registration)
    {
        static::log($msg, ['user' => $registration->get_user_id(), 'event' => $registration->get_event_post_id()]);
    }

    /**
     * @return string
     */
    public static function get_option_name()
    {
        return self::$option_name;
    }
}
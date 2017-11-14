<?php

/**
 * @author Christoph Bessei
 * @version
 */
class Ems_Event_Registration extends Ems_Log
{
    const MAIL_TYPE_REGISTRATION = 1;
    const MAIL_TYPE_DELETE_REGISTRATION = 2;

    protected static $option_name = 'ems_event_registration';

    private $event_post_id;
    private $user_id;
    /**
     * @var $data
     * Array of fields which belongs to the registration. Could be used for event specific information for the participants list
     */
    private $data;

    public function __construct($event_post_id, $user_id, $data = [])
    {
        $this->event_post_id = $event_post_id;
        $this->user_id = $user_id;
        $this->data = $data;
    }

    /**
     * @return int
     */
    public function get_event_post_id()
    {
        return (int)$this->event_post_id;
    }

    /**
     * @return int
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
        if ($otherObject->get_event_post_id() == $this->get_event_post_id() && $otherObject->get_user_id(
            ) == $this->get_user_id()) {
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
            (new \BIT\EMS\Service\Event\Registration\MailService())->sendRegisterMail($registration);
            static::logEventRegistration('Sent add event registration mail.', $registration);
        } else {
            static::logEventRegistration("Couldn't add event registration.", $registration);
        }
    }


    /**
     * @param \Ems_Event_Registration $registration
     * @throws \Exception
     */
    public static function delete_event_registration(Ems_Event_Registration $registration)
    {
        (new \BIT\EMS\Service\Event\Registration\RegistrationService())->removeByEventRegistration($registration);
    }

    /**
     * @return Ems_Event_Registration[]
     */
    protected static function get_event_registrations()
    {
        $registrations = get_option(self::$option_name);
        if (is_array($registrations)) {
            return $registrations;
        }
        return [];
    }

    /**
     * @param $event_post_id
     *
     * @return Ems_Event_Registration[]
     */
    public static function get_registrations_of_event($event_post_id)
    {
        $registrations = self::get_event_registrations();
        $event_registrations = [];
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
        $event_registrations = [];
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
            if ($registration->get_event_post_id() == $cur_registration->get_event_post_id(
                ) && $registration->get_user_id() == $cur_registration->get_user_id()) {
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
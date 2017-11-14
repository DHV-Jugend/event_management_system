<?php

/**
 * @author Christoph Bessei
 * @version
 * @deprecated Use namespaced EventRegistration.
 * This class is only kept for backward compatibility (there are serialized Ems_Event_Registration objects in option "ems_event_registration")
 *
 */
class Ems_Event_Registration extends Ems_Log
{
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
}
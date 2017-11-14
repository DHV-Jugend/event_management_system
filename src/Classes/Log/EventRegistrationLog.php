<?php
namespace BIT\EMS\Log;

use Ems_Event_Registration;

/**
 * @author Christoph Bessei
 */
class EventRegistrationLog extends AbstractLog
{
    /**
     * @var array
     */
    protected $additionalLogFields = [
        'user' => 'int(11) NOT NULL',
        'event' => 'int(11) NOT NULL',
    ];

    /**
     * @param string $msg
     * @param \Ems_Event_Registration $registration
     */
    public function info(string $msg, Ems_Event_Registration $registration)
    {
        $this->insert(
            AbstractLog::LOG_LEVEL_INFO,
            $msg,
            [
                'user' => $registration->get_user_id(),
                'event' => $registration->get_event_post_id(),
            ]
        );
    }

    /**
     * @param string $msg
     * @param \Ems_Event_Registration $registration
     */
    public function error(string $msg, Ems_Event_Registration $registration)
    {
        $this->insert(
            AbstractLog::LOG_LEVEL_ERROR,
            $msg,
            [
                'user' => $registration->get_user_id(),
                'event' => $registration->get_event_post_id(),
            ]
        );
    }
}

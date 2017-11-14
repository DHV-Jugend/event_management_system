<?php
namespace BIT\EMS\Log;

use BIT\EMS\Domain\Model\EventRegistration;

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
     * @param EventRegistration $registration
     */
    public function info(string $msg, EventRegistration $registration)
    {
        $this->insert(
            AbstractLog::LOG_LEVEL_INFO,
            $msg,
            [
                'user' => $registration->getUserId(),
                'event' => $registration->getEventId(),
            ]
        );
    }

    /**
     * @param string $msg
     * @param EventRegistration $registration
     */
    public function error(string $msg, EventRegistration $registration)
    {
        $this->insert(
            AbstractLog::LOG_LEVEL_ERROR,
            $msg,
            [
                'user' => $registration->getUserId(),
                'event' => $registration->getEventId(),
            ]
        );
    }
}

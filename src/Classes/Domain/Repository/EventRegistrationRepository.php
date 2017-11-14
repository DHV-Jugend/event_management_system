<?php
/**
 * @author Christoph Bessei
 */

namespace BIT\EMS\Domain\Repository;


use BIT\EMS\Domain\Model\EventRegistration;
use BIT\EMS\Exception\Event\EventRegistrationNotFoundException;
use BIT\EMS\Log\EventRegistrationLog;
use BIT\EMS\Model\Event;
use Ems_Event_Registration;

class EventRegistrationRepository extends AbstractRepository
{
    protected static $option_name = 'ems_event_registration';

    /**
     * @var \BIT\EMS\Log\EventRegistrationLog
     */
    protected $eventRegistrationLog;

    public function __construct()
    {
        $this->eventRegistrationLog = new EventRegistrationLog();
    }

    public function findAll()
    {
        $registrations = get_option(self::$option_name);
        if (is_array($registrations)) {
            return $registrations;
        }
        return [];
    }

    /**
     * @param \BIT\EMS\Model\Event $event
     * @return Ems_Event_Registration[]
     */
    public function findByEvent(Event $event): array
    {
        $eventId = $event->getID();
        /** @var EventRegistration[] $registrations */
        $registrations = get_option(EventRegistration::get_option_name());
        $event_registrations = [];
        foreach ($registrations as $registration) {
            if ($registration->get_event_post_id() === $eventId) {
                $event_registrations[] = $registration;
            }
        }
        return $event_registrations;
    }

    public function findByParticipant($user_id): array
    {
        $registrations = $this->findAll();
        $event_registrations = [];
        foreach ($registrations as $registration) {
            if ($registration->get_user_id() == $user_id) {
                $event_registrations[] = $registration;
            }
        }
        return $event_registrations;
    }

    public function findByEventAndParticipant(int $eventId, int $participantId): EventRegistration
    {
        $registrations = $this->findAll();
        foreach ($registrations as $registration) {
            if ($registration->get_user_id() == $participantId && $eventId == $registration->get_event_post_id()) {
                return $registration;
            }
        }
        throw new EventRegistrationNotFoundException(
            'No event registration found. Event: ' . $eventId . ' Participant: ' . $participantId
        );
    }

    public function add(Ems_Event_Registration $eventRegistration)
    {
        $registrations = $this->findAll();
        $registrations[] = $eventRegistration;

        return update_option(self::$option_name, $registrations);
    }

    public function removeByEventRegistration(Ems_Event_Registration $registration)
    {
        $registrations = $this->findAll();

        foreach ($registrations as $key => $cur_registration) {
            if ($registration->equals($cur_registration)) {
                unset($registrations[$key]);
                break;
            }
        }

        return update_option(static::$option_name, $registrations);
    }

    public function removeByEventAndParticipant(Event $event, $participant)
    {
        $eventRegistrations = $this->findByParticipant($participant);
        if (is_array($eventRegistrations)) {
            foreach ($eventRegistrations as $eventRegistration) {
                if ($event === intval($eventRegistration->get_event_post_id())) {
                    return $this->removeByEventRegistration($eventRegistration);
                }
            }
        }

        throw new EventRegistrationNotFoundException(
            'No event registration found. Event: ' . $event->getID() . ' Participant: ' . $participant
        );
    }
}

<?php
/**
 * @author Christoph Bessei
 */

namespace BIT\EMS\Domain\Repository;


use BIT\EMS\Model\Event;

class EventRegistrationRepository extends AbstractRepository
{
    public function findByEvent(Event $event)
    {
        $eventId = $event->getID();
        /** @var \Ems_Event_Registration[] $registrations */
        $registrations = get_option(\Ems_Event_Registration::get_option_name());
        $event_registrations = [];
        foreach ($registrations as $registration) {
            if ($registration->get_event_post_id() === $eventId) {
                $event_registrations[] = $registration;
            }
        }
        return $event_registrations;
    }
}
<?php
namespace BIT\EMS\Service\Event;

use Ems_Event_Registration;

/**
 * @author Christoph Bessei
 */
class Registration
{
    public function removeByParticipantAndEvent(int $event, int $participant)
    {
        $eventRegistrations = Ems_Event_Registration::get_registrations_of_user($participant);
        if (is_array($eventRegistrations)) {
            foreach ($eventRegistrations as $eventRegistration) {
                if ($event === intval($eventRegistration->get_event_post_id())) {
                    Ems_Event_Registration::delete_event_registration($eventRegistration);
                    return true;
                }
            }
        }
        return false;
    }
}

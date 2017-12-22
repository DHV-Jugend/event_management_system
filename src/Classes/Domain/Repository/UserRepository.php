<?php
namespace BIT\EMS\Domain\Repository;

/**
 * @author Christoph Bessei
 */
class UserRepository extends AbstractRepository
{
    public function findAllUserWithEventManagerCapabilities(): array
    {
        // Sadly get_users can't filter by capability,
        // so we load every user who has a role with "more" capabilities than Subscribers and then filter with PHP
        $eventManagers = get_users(['role__not_in' => ['Subscriber'],]);
        /** @var \WP_User $eventManager */
        foreach ($eventManagers as $key => $eventManager) {
            if (!$eventManager->has_cap('edit_event') && !$eventManager->has_cap("edit_ems_event")) {
                unset($eventManagers[$key]);
            }
        }

        return $eventManagers;
    }
}

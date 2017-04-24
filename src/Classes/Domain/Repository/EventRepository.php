<?php

namespace BIT\EMS\Domain\Repository;

use BIT\EMS\Model\Event;

/**
 * @author Christoph Bessei
 * @version
 */
class EventRepository extends AbstractRepository
{
    /**
     * @param int $id
     * @return \BIT\EMS\Model\Event
     */
    public function findEventById(int $id)
    {
        $wpPost = get_post($id);
        return new Event($wpPost);
    }
}
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

    /**
     * @return Event[]
     */
    public function findAll()
    {
        $args = [
            'post_type' => \Ems_Event::get_post_type(),
            'posts_per_page' => -1,
        ];
        $posts = get_posts($args);
        $events = [];
        if (is_array($posts)) {
            foreach ($posts as $post) {
                $events[] = new Event($post);
            }
        }
        return $events;
    }
}
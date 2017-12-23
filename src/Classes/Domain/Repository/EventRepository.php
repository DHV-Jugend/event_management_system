<?php

namespace BIT\EMS\Domain\Repository;

use BIT\EMS\Domain\Model\Event\EventMetaBox;
use BIT\EMS\Model\Event;
use BIT\EMS\Utility\DateTimeUtility;

/**
 * @author Christoph Bessei
 * @version
 */
class EventRepository extends AbstractRepository
{

    public function findEventManagerMail($event): ?string
    {
        if (is_object($event)) {
            $event = $event->ID;
        }

        $leader = get_userdata(get_post_meta($event, EventMetaBox::EVENT_MANAGER, true));

        if ($leader instanceof \WP_User) {
            $leader_email = $leader->user_email;
        } else {
            $leader_email = get_post_meta($event, EventMetaBox::EVENT_MANAGER_CUSTOM_MAIL, true);
        }

        return $leader_email;
    }

    public function findEventStartDate($event): ?\DateTime
    {
        if (is_object($event)) {
            $event = $event->ID;
        }
        $startDate = get_post_meta($event, EventMetaBox::EVENT_START_DATE, true);
        return DateTimeUtility::toDateTime($startDate);
    }

    public function findEventEndDate($event): ?\DateTime
    {
        if (is_object($event)) {
            $event = $event->ID;
        }
        $endDate = get_post_meta($event, EventMetaBox::EVENT_END_DATE, true);
        return DateTimeUtility::toDateTime($endDate);
    }

    public function findEventsInPeriod(?\Ems_Date_Period $startDatePeriod, ?\Ems_Date_Period $endDatePeriod): array
    {
        $startDateConstraint = [];
        if (!is_null($startDatePeriod)) {
            $startDateConstraint = [
                'key' => 'ems_start_date',
                'value' => [
                    $startDatePeriod->get_start_date()->getTimestamp(),
                    $startDatePeriod->get_end_date()->getTimestamp(),
                ],
                'type' => 'numeric',
                'compare' => 'BETWEEN',
            ];
        }

        $endDateConstraint = [];
        if (!is_null($endDatePeriod)) {
            $endDateConstraint = [
                'key' => 'ems_end_date',
                'value' => [
                    $endDatePeriod->get_start_date()->getTimestamp(),
                    $endDatePeriod->get_end_date()->getTimestamp(),
                ],
                'compare' => 'BETWEEN',
            ];
        }

        $args = [
            'post_type' => \Ems_Event::get_post_type(),
            'posts_per_page' => -1,
            'orderby' => [
                'ems_start_date' => 'ASC',
                'ems_end_date' => 'ASC',
            ],
        ];

        $constraints = [];

        if (!empty($startDateConstraint) && !empty($endDateConstraint)) {
            $constraints['meta_query'] = [
                'relation' => 'AND',
                $startDateConstraint,
                $endDateConstraint,
            ];
        } elseif (!empty($startDateConstraint)) {
            $constraints['meta_query'] = [$startDateConstraint];
        } elseif (!empty($endDateConstraint)) {
            $constraints['meta_query'] = [$endDateConstraint];
        }

        $args = array_merge($args, $constraints);

        $query = new \WP_Query($args);
        $posts = $query->posts;

        if (!is_array($posts)) {
            $posts = [];
        }

        foreach ($posts as $key => $post) {
            $posts[$key] = new Event($post);
        }

        return $posts;
    }

    /**
     * @param int $id
     * @return \BIT\EMS\Model\Event
     */
    public function findEventById(int $id)
    {
        $post = get_post($id);
        if (empty($post)) {
            // TODO: Throw Exception?
            return null;
        }
        return new Event($post);
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
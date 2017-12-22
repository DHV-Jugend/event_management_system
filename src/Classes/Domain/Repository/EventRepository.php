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
        $startDateConstraint = null;
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

        $endDateConstraint = null;
        if (!is_null($endDatePeriod)) {
            $endDateConstraint = [
                'key' => 'ems_start_end',
                'value' => [
                    $endDatePeriod->get_start_date()->getTimestamp(),
                    $endDatePeriod->get_end_date()->getTimestamp(),
                ],
                'type' => 'numeric',
                'compare' => 'BETWEEN',
            ];
        }

        $args = [
            'post_type' => \Ems_Event::get_post_type(),
            'posts_per_page' => -1,

            'meta_key' => 'ems_start_date',
            'meta_value' => [
                $startDatePeriod->get_start_date()->getTimestamp() . '',
                $startDatePeriod->get_end_date()->getTimestamp() . '',
            ],
            'meta_compare' => 'BETWEEN',
            'orderby' => [
                'ems_start_date' => 'ASC',
                'ems_start_end' => 'ASC',
            ],
        ];

        //        if (!is_null($startDateConstraint) && !is_null($endDateConstraint)) {
        //            $args['meta_query'] = [
        //                'relation' => 'AND',
        //                $startDateConstraint,
        //                $endDateConstraint,
        //            ];
        //        } elseif (!is_null($startDateConstraint)) {
        //            $args['meta_query'] = [$startDateConstraint];
        //        } elseif (!is_null($endDateConstraint)) {
        //            $args['meta_query'] = [$endDateConstraint];
        //        }

        $query = new \WP_Query($args);
        $posts = $query->get_posts();
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
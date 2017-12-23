<?php
namespace BIT\EMS\Migration\Version;

use BIT\EMS\Domain\Model\Event\EventMetaBox;
use BIT\EMS\Migration\MigrationInterface;
use BIT\EMS\Model\Event;

/**
 * @author Christoph Bessei
 */
class Migration_0_2_1 implements MigrationInterface
{
    /**
     * @var  string
     */
    protected $resultMessage;

    public function getDescription(): string
    {
        return 'Converted event start and end date to timestamp';
    }

    public function getResultMessage(): string
    {
        return $this->resultMessage;
    }

    public function run()
    {
        \Ems_Event::get_post_type();
        $updatedEntries = 0;
        $events = get_posts(['post_type' => Event::get_post_type(), 'post_status' => 'any', 'numberposts' => -1]);
        $checkedEvents = [];
        if (is_array($events)) {
            /** @var \WP_Post $event */
            foreach ($events as $event) {

                $updated = false;
                $startDate = get_post_meta($event->ID, EventMetaBox::EVENT_START_DATE, true);
                if ($startDate instanceof \DateTime) {
                    update_post_meta($event->ID, EventMetaBox::EVENT_START_DATE, $startDate->getTimestamp());
                    $updated = true;
                }

                $endDate = get_post_meta($event->ID, EventMetaBox::EVENT_END_DATE, true);
                if ($endDate instanceof \DateTime) {
                    update_post_meta($event->ID, EventMetaBox::EVENT_END_DATE, $endDate->getTimestamp());
                    $updated = true;
                }

                if ($updated) {
                    $updatedEntries++;
                }

                $checkedEvents[] = $event->post_title . ' (' . $event->ID . ')';
            }
        }

        $this->resultMessage = 'Migrated ' . $updatedEntries . ' events to new date format.<br>';
        $this->resultMessage .= 'Checked events:<br>' . implode('<br>', $checkedEvents);
    }
}

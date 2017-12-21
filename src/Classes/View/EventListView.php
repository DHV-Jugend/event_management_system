<?php
namespace BIT\EMS\View;

use BIT\EMS\Settings\Tab\AdvancedTab;
use BIT\EMS\Utility\ParticipantUtility;

/**
 * @author Christoph Bessei
 * @version
 */
class EventListView extends BaseView
{
    public function printContent()
    {
        /** @var \Ems_Event[] $events */
        $events = $this->arguments["events"];
        ?>
        <div class="ems_event_wrapper">
            <?php
            foreach ($events as $event) {
                // TODO: Move filter to Service(?)
                if (is_object($event->get_end_date_time()) && $event->get_end_date_time()->getTimestamp() < time()) {
                    continue;
                }
                $dateFormat = AdvancedTab::get(AdvancedTab::EVENT_LIST_DATE_FORMAT);
                if (empty($dateFormat)) {
                    $dateFormat = get_option('date_format');
                }
                $date_string = $event->getFormattedDateString($dateFormat);

                $participantLevelIcons = ParticipantUtility::getParticipantLevelIcons($event);
                $participantTypeIcons = ParticipantUtility::getParticipantTypeIcons($event);
                ?>
                <div class="ems_event_entry">
                    <a href="<?php echo get_permalink($event->ID); ?>"><?php echo $event->post_title; ?></a>

                    <div class="ems_event_entry_date"><i><?php echo $date_string; ?> </i></div>
                    <div class="ems_event_icon_wrapper">
                        <div class="ems_event_participant_level_icon_wrapper">
                            <a href="#ems_event_icon_legend">
                                <?php
                                foreach ($participantLevelIcons as $participantLevelIcon) {
                                    ?><img title="<?php echo $participantLevelIcon["title"] ?>"
                                           class="masterTooltip"
                                           src="<?php echo $participantLevelIcon["path"] ?>"/><?php
                                }
                                ?>
                            </a>
                        </div>
                        <div class="ems_event_participant_type_icon_wrapper">
                            <a href="#ems_event_icon_legend">
                                <?php
                                foreach ($participantTypeIcons as $participantTypeIcon) {
                                    ?><img class="masterTooltip"
                                           title="<?php echo $participantTypeIcon["title"] ?>"
                                           src="<?php echo $participantTypeIcon["path"] ?>"/><?php
                                }
                                ?>
                            </a>
                        </div>
                    </div>
                </div>
                <?php
            }
            ?>
        </div>
        <?php
    }
}
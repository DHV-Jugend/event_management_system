<?php
namespace BIT\EMS\View;

use BIT\EMS\View\Base\Base;
use Ems_Participant_Utility;

/**
 * @author Christoph Bessei
 * @version
 */
class EventList extends Base
{
    public function printContent()
    {
        /** @var \Ems_Event[] $events */
        $events = $this->arguments["events"];
        ?>
        <div class="ems_event_wrapper">
            <?php
            foreach ($events as $event) {
                if (is_object($event->get_end_date_time()) && $event->get_end_date_time()->getTimestamp() < time()) {
                    continue;
                }
                $dateFormat = "d.m.y";
                $date_string = $event->getFormattedDateString($dateFormat);

                $participantLevelIcons = Ems_Participant_Utility::getParticipantLevelIcons($event);
                $participantTypeIcons = Ems_Participant_Utility::getParticipantTypeIcons($event);
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
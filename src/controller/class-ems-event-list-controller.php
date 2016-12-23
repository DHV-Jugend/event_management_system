<?php

/**
 * @author  Christoph Bessei
 * @version 0.04
 */
class Ems_Event_List_Controller
{

    public static function get_event_list()
    {
        wp_enqueue_style('ems-general', Event_Management_System::get_plugin_url() . "css/ems_general.css");
        wp_enqueue_script('ems-tooltip', Event_Management_System::get_plugin_url() . "js/ems-tooltip.js");

        $allowed_event_time_start = new DateTime();
        $allowed_event_time_start->setTimestamp(Ems_Date_Helper::get_timestamp(get_option("date_format"), get_option("ems_start_date_period")));
        $allowed_event_time_end = new DateTime();
        $allowed_event_time_end->setTimestamp(Ems_Date_Helper::get_timestamp(get_option("date_format"), get_option("ems_end_date_period")));
        $allowed_event_time_period = new Ems_Date_Period($allowed_event_time_start, $allowed_event_time_end);
        $events = Ems_Event::get_events(-1, true, false, null, array(), $allowed_event_time_period);

        ?>
        <div class="ems_event_wrapper">
            <?php
            foreach ($events as $event) {
                if(is_object($event->get_end_date_time()) && $event->get_end_date_time()->getTimestamp() < time()) {
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
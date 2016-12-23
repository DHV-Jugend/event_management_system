<?php

/**
 * @author Christoph Bessei
 * @version
 */
class Ems_Event_Shortcode_Event_Header_Controller implements Ems_Shortcode_Controller_Interface
{
    public static function addShortcode()
    {
        add_shortcode(Ems_Conf::EMS_NAME_PREFIX . 'event_header', array(
            self::class,
            'replaceShortcode'
        ));
    }

    public static function replaceShortcode($atts)
    {
        wp_enqueue_style('ems-general', Event_Management_System::get_plugin_url() . "css/ems_general.css");
        wp_enqueue_script('ems-tooltip', Event_Management_System::get_plugin_url() . "js/ems-tooltip.js");

        $event = Ems_Event::get_event(get_the_ID());
        $participantLevelIcons = Ems_Participant_Utility::getParticipantLevelIcons($event);
        $participantTypeIcons = Ems_Participant_Utility::getParticipantTypeIcons($event);
        $dateString = $event->getFormattedDateString(get_option("date_format"));
        ob_start();
        ?>
        <p><i><?php echo $dateString ?></i></p>
        <div class="ems_single_event_icon_wrapper">
            <div class="ems_single_event_icon_column_wrapper">
                <?php
                foreach ($participantLevelIcons as $participantLevelIcon) {
                    ?><img title="<?php echo $participantLevelIcon["title"] ?>"
                           class="masterTooltip ems_event_header_icon"
                           src="<?php echo $participantLevelIcon["path"] ?>"/><?php
                }
                ?>
            </div>
            <div class="ems_single_event_icon_column_wrapper">
                <?php
                foreach ($participantTypeIcons as $participantTypeIcon) {
                    ?><img title="<?php echo $participantTypeIcon["title"] ?>"
                           class="masterTooltip ems_event_header_icon"
                           src="<?php echo $participantTypeIcon["path"] ?>"/><?php
                }
                ?>
            </div>
        </div>
        <?php
        echo Ems_Event_Shortcode_Event_Registration_Link_Controller::replaceShortcode(null);
        return ob_get_clean();
    }
}
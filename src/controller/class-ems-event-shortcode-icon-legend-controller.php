<?php

/**
 * @author Christoph Bessei
 * @version
 */
class Ems_Event_Shortcode_Icon_Legend_Controller implements Ems_Shortcode_Controller_Interface
{
    public static function addShortcode()
    {
        add_shortcode(Ems_Conf::EMS_NAME_PREFIX . 'event_icon_legend', array(
            self::class,
            'replaceShortcode'
        ));
    }

    public static function replaceShortcode($atts)
    {
        $imagePath = Event_Management_System::get_plugin_url() . "images/";
        ob_start();
        ?>
        <div id="ems_event_icon_legend" class="ems_event_icon_legend_wrapper">
            <h2>Legende</h2>

            <div class="ems_event_icon_legend_column">
                <div class="ems_event_icon_legend_entry">
                    <span class="ems_event_icon_legend_entry_text">Einsteiger:</span>
                    <img src="<?php echo $imagePath ?>participant_level_beginner_yes.png">
                </div>
                <div class="ems_event_icon_legend_entry">
                    <span class="ems_event_icon_legend_entry_text">Genussflieger:</span>
                    <img src="<?php echo $imagePath ?>participant_level_intermediate_yes.png">
                </div>
                <div class="ems_event_icon_legend_entry">
                    <span class="ems_event_icon_legend_entry_text">Ambitionierte:</span>
                    <img src="<?php echo $imagePath ?>participant_level_pro_yes.png">
                </div>
            </div>
            <div class="ems_event_icon_legend_column">
                <div class="ems_event_icon_legend_entry">
                    <span class="ems_event_icon_legend_entry_text">Geeignet:</span>
                    <img src="<?php echo $imagePath ?>participant_level_beginner_yes.png">
                </div>
                <div class="ems_event_icon_legend_entry">
                    <span class="ems_event_icon_legend_entry_text">Teilweise geeignet:</span>
                    <img src="<?php echo $imagePath ?>participant_level_beginner_partly.png">
                </div>
                <div class="ems_event_icon_legend_entry">
                    <span class="ems_event_icon_legend_entry_text">Nicht geeignet:</span>
                    <img src="<?php echo $imagePath ?>participant_level_beginner_no.png">
                </div>
            </div>
        </div>
        <?php
        return ob_get_clean();
    }
}
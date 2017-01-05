<?php

/**
 * @author Christoph Bessei
 * @version
 */
class Ems_Script_Enqueue
{
    /**
     * Enqueue script on admin page if needed
     */
    public static function admin_enqueue_script($hook_suffix)
    {
        //Check if page is event editor
        if (Ems_Event::get_post_type() === get_post_type()) {
            //Load jquery, datepicker and register styles
            wp_enqueue_script('jquery');
            wp_enqueue_script('jquery-ui-datepicker');

            wp_enqueue_style('ems_smoothness_jquery_css', Event_Management_System::getAssetsBaseUrl() . 'css/ems_datepicker_ui.css');

            wp_enqueue_script('ems_datepicker_period', Event_Management_System::getAssetsBaseUrl() . "js/ems_datepicker_period.js", array('jquery-ui-datepicker'));
            $localized = Ems_Javascript_Helper::get_localized_datepicker_options();
            wp_localize_script('ems_datepicker_period', 'objectL10n', $localized);
        }
    }
} 
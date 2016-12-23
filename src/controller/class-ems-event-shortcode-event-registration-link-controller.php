<?php

/**
 * @author Christoph Bessei
 * @version
 */
class Ems_Event_Shortcode_Event_Registration_Link_Controller implements Ems_Shortcode_Controller_Interface
{
    public static function addShortcode()
    {
        add_shortcode(Ems_Conf::EMS_NAME_PREFIX . 'event_registration_link', array(
            self::class,
            'replaceShortcode'
        ));
    }

    public static function replaceShortcode($atts)
    {
        $eventID = (isset($atts["ID"]) && !empty($atts["ID"])) ? intval($atts["ID"]) : get_the_ID();
        $params = array('event' => "ID_" . $eventID);

        $eventRegistrationID = get_option(Fum_Conf::$fum_event_registration_page);
        $url = get_permalink($eventRegistrationID);
        $url = add_query_arg($params, $url);
        ob_start();
        ?>
        <p><a href="<?php echo $url ?>">Hier gehts zur Anmeldung</a></p>
        <?php
        return ob_get_clean();
    }
}
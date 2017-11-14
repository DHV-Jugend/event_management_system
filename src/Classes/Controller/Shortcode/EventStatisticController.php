<?php
/**
 * @author Christoph Bessei
 * @version
 */

namespace BIT\EMS\Controller\Shortcode;

use Ems_Event;

class EventStatisticController extends AbstractShortcodeController
{
    protected $aliases = [\Ems_Conf::PREFIX . 'event_statistic'];

    public function printContent($atts = [], $content = null)
    {
        //If user has no access, redirect to home
        if (!current_user_can(Ems_Event::get_edit_capability())) {
            wp_redirect(home_url());
            exit;
        }

        echo "Couldn't create statistic, function is currently disabled";
    }
}
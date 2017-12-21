<?php
/**
 * @author Christoph Bessei
 * @version
 */

namespace BIT\EMS\Controller\Shortcode;


use BIT\EMS\View\EventListView;
use DateTime;
use Ems_Date_Helper;
use Ems_Date_Period;
use Ems_Event;

class EventListController extends AbstractShortcodeController
{
    protected function addCss()
    {
        wp_enqueue_style('ems-general', $this->getCssUrl("ems_general"));
    }

    protected function addJs()
    {
        wp_enqueue_script('ems-tooltip', $this->getJsUrl("ems_tooltip"));
    }

    public function printContent($atts = [], $content = null)
    {
        $allowed_event_time_start = new DateTime();
        $allowed_event_time_start->setTimestamp(Ems_Date_Helper::get_timestamp(get_option("date_format"), get_option("ems_start_date_period")));

        $allowed_event_time_end = new DateTime();
        $allowed_event_time_end->setTimestamp(Ems_Date_Helper::get_timestamp(get_option("date_format"), get_option("ems_end_date_period")));

        $allowed_event_time_period = new Ems_Date_Period($allowed_event_time_start, $allowed_event_time_end);

        $events = Ems_Event::get_events(-1, true, false, null, array(), $allowed_event_time_period);

        (new EventListView(["events" => $events]))->printContent();
    }
}
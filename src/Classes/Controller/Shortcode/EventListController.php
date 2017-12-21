<?php
/**
 * @author Christoph Bessei
 * @version
 */

namespace BIT\EMS\Controller\Shortcode;


use BIT\EMS\Settings\Tab\BasicTab;
use BIT\EMS\Utility\DateTimeUtility;
use BIT\EMS\View\EventListView;
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

    /**
     * @param array $atts
     * @param null $content
     * @throws \Exception
     */
    public function printContent($atts = [], $content = null)
    {

        $startDatePeriod = BasicTab::get(BasicTab::EVENT_START_DATE);
        $endDatePeriod = BasicTab::get(BasicTab::EVENT_END_DATE);

        $allowed_event_time_period = DateTimeUtility::toDateTimePeriod($startDatePeriod, $endDatePeriod);

        $events = Ems_Event::get_events(-1, true, false, null, [], $allowed_event_time_period);

        (new EventListView(["events" => $events]))->printContent();
    }
}
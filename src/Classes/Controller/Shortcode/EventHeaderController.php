<?php
/**
 * @author Christoph Bessei
 * @version
 */

namespace BIT\EMS\Controller\Shortcode;

use BIT\EMS\Utility\ParticipantUtility;
use BIT\EMS\View\EventHeaderView;
use Ems_Event;

class EventHeaderController extends AbstractShortcodeController
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
        $event = Ems_Event::get_event(get_the_ID());
        $participantLevelIcons = ParticipantUtility::getParticipantLevelIcons($event);
        $participantTypeIcons = ParticipantUtility::getParticipantTypeIcons($event);
        $dateString = $event->getFormattedDateString(get_option("date_format"));

        $arguments = [
            "participantLevelIcons" => $participantLevelIcons,
            "participantTypeIcons" => $participantTypeIcons,
            "dateString" => $dateString
        ];

        (new EventHeaderView($arguments))->printContent();

        // Add registration link
        (new EventRegistrationLinkController())->printContent();
    }
}
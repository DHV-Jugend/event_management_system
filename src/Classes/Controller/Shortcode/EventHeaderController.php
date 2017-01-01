<?php
/**
 * @author Christoph Bessei
 * @version
 */

namespace BIT\EMS\Controller\Shortcode;

use BIT\EMS\View\EventHeaderView;
use Ems_Event;
use Ems_Participant_Utility;

class EventHeaderController extends AbstractShortcodeController
{
    protected function addCss()
    {
        wp_enqueue_style('ems-general', $this->getCssUrl("ems_general"));
    }

    protected function addJs()
    {
        wp_enqueue_script('ems-tooltip', $this->getJsUrl("ems-tooltip"));
    }

    public function printContent($atts = [], $content = null)
    {
        $event = Ems_Event::get_event(get_the_ID());
        $participantLevelIcons = Ems_Participant_Utility::getParticipantLevelIcons($event);
        $participantTypeIcons = Ems_Participant_Utility::getParticipantTypeIcons($event);
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
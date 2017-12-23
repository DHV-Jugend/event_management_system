<?php
/**
 * @author Christoph Bessei
 * @version
 */

namespace BIT\EMS\Controller\Shortcode;


use BIT\EMS\Service\Event\EventService;
use BIT\EMS\View\EventListView;

class EventListController extends AbstractShortcodeController
{
    /**
     * @var \BIT\EMS\Service\Event\EventService
     */
    protected $eventService;

    public function __construct()
    {
        $this->eventService = new EventService();
    }

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
        (new EventListView(["events" => $this->eventService->determineCurrentListViewEvents()]))->printContent();
    }
}
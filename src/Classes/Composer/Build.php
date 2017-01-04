<?php
namespace BIT\EMS\Composer;

use Composer\Script\Event;

/**
 * @author Christoph Bessei
 * @version
 */
class Build
{
    public static function run(Event $event)
    {
        $build = new static();
        if ($event->isDevMode()) {
            $build->runDev($event);
        } else {
            $build->runProd($event);
        }
    }

    public function runDev(Event $event)
    {
        passthru("grunt");
    }

    public function runProd(Event $event)
    {
        passthru("grunt dist");
    }
}
<?php
namespace BIT\EMS\Schedule;

use BIT\EMS\Schedule\Base\Base;
use DirectoryIterator;
use Event_Management_System;

/**
 * @author Christoph Bessei
 * @version
 */
class CleanTempFiles extends Base
{
    /**
     * @var array
     */
    protected static $additionalIntervals = [
        '5min' => [
            'interval' => 5,
            'display' => 'Every 5 minutes'
        ]
    ];

    /**
     * @var string
     */
    protected $recurrence = '5min';

    public function run()
    {
        $tempDownloads = Event_Management_System::get_plugin_path() . "tempDownloads/";
        if (file_exists($tempDownloads)) {
            foreach (new DirectoryIterator($tempDownloads) as $fileInfo) {
                if ($fileInfo->isDot()) {
                    continue;
                }
                if (time() - $fileInfo->getCTime() >= WEEK_IN_SECONDS) {
                    unlink($fileInfo->getRealPath());
                }
            }
        }
    }
}
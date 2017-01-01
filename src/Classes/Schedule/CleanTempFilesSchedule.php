<?php
namespace BIT\EMS\Schedule;

use BIT\EMS\Utility\GeneralUtility;
use DirectoryIterator;
use Event_Management_System;

/**
 * @author Christoph Bessei
 * @version
 */
class CleanTempFilesSchedule extends AbstractSchedule
{
    public function run()
    {
        $tempDownloads = Event_Management_System::get_plugin_path() . "tempDownloads/";
        if (file_exists($tempDownloads)) {
            foreach (new DirectoryIterator($tempDownloads) as $fileInfo) {
                // Ignore special files and hidden files (.htaccess, .gitignore etc)
                if ($fileInfo->isDot() || GeneralUtility::startsWith($fileInfo->getFilename(), '.')) {
                    continue;
                }
                if (time() - $fileInfo->getMTime() >= DAY_IN_SECONDS) {
                    unlink($fileInfo->getRealPath());
                }
            }
        }
    }
}
<?php
/**
 * @author Christoph Bessei
 * @version
 */

namespace BIT\EMS\Utility;


use Ems_Event;
use Event_Management_System;

class ParticipantUtility
{
    /**
     * @param Ems_Event $event
     * @return array
     */
    public static function getParticipantLevelIcons(Ems_Event $event)
    {
        $participantLevels = $event->getParticipantLevels();
        if (!is_array($participantLevels)) {
            return array();
        }

        $imageBaseUrl = Event_Management_System::getAssetsBaseUrl() . "img/";
        $participantIcons = array();

        foreach ($participantLevels as $participantLevel) {
            $title = "Für ";
            switch ($participantLevel->getKey()) {
                case "beginner":
                    $title .= "Einsteiger ";
                    break;
                case "intermediate":
                    $title .= "Genussflieger ";
                    break;
                case "pro":
                    $title .= "Ambitionierte ";
                    break;

            }
            $path = $imageBaseUrl . "participant_level_" . $participantLevel->getKey() . "_";
            switch ($participantLevel->getValue()) {
                case 0:
                    $path .= "no";
                    $title .= "nicht geeignet.";
                    break;
                case 0.5;
                    $path .= "partly";
                    $title .= "teilweise geeignet.";
                    break;
                case 1:
                    $path .= "yes";
                    $title .= "geeignet.";
                    break;
                default:
                    //Invalid value, ignore
                    error_log("Invalid participant level value: " .
                        $participantLevel->getValue() . " for participant level " .
                        $participantLevel->getLabel());
                    continue;
            }
            $path .= ".png";
            $participantIcons[] = array("path" => $path, "title" => $title);
        }
        return $participantIcons;
    }

    /**
     * @param Ems_Event $event
     * @return array
     */
    public static function getParticipantTypeIcons(Ems_Event $event)
    {
        $participantTypes = $event->getParticipantTypes();
        if (!is_array($participantTypes)) {
            return array();
        }
        $imageBaseUrl = Event_Management_System::getAssetsBaseUrl() . "img/";
        $participantIcons = array();

        foreach ($participantTypes as $participantType) {
            $title = "Für ";
            switch ($participantType->getKey()) {
                case "paraglider":
                    $title .= "Gleitschirme ";
                    break;
                case "hangglider":
                    $title .= "Drachen ";
                    break;
                case "pedestrian":
                    $title .= "Fußgänger ";
                    break;
            }
            $path = $imageBaseUrl . "participant_type_" . $participantType->getKey() . "_";
            if ($participantType->getValue()) {
                $path .= "yes";
                $title .= "geeignet.";
            } else {
                $path .= "no";
                $title .= "nicht geeignet.";
            }
            $path .= ".png";
            $participantIcons[] = array("path" => $path, "title" => $title);
        }
        return $participantIcons;
    }
}
<?php

/**
 * @author Christoph Bessei
 * @version
 */
class Ems_Date_Helper
{

    /**
     * @return array Returns an array with "January","February" etc. does not depend on localization
     */
    public static function get_months()
    {
        return [
            'January',
            'February',
            'March',
            'April',
            'May',
            'June',
            'July',
            'August',
            'September',
            'October',
            'November',
            'December',
        ];
    }


    /**
     * Returns the timestamp of a date even if this date uses local month names
     *
     * @param string $format format of $date, uses the format of php date()
     * @param string $date
     *
     * @return int unix timestamp of $date
     */
    public static function get_timestamp($format, $date)
    {
        foreach (self::get_months() as $month) {
            $date = str_replace(__($month), $month, $date);
        }

        $date = date_parse_from_format($format, $date);
        $date = mktime(
            $date['hour'],
            $date['minute'],
            $date['second'],
            $date['month'],
            $date['day'],
            $date['year']
        );

        return $date;
    }

    public static function buildDateTime($format, $date): \DateTime
    {
        $timestamp = static::get_timestamp($format, $date);
        return (new DateTime())->setTimestamp($timestamp);
    }

}
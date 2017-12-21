<?php
namespace BIT\EMS\Utility;

use Carbon\Carbon;

/**
 * @author Christoph Bessei
 */
class DateTimeUtility
{
    public static function toDateTime($value): ?\DateTime
    {
        // Already \DateTime
        if ($value instanceof \DateTime) {
            return $value;
        }

        // Timestamp
        if (is_numeric($value)) {
            return Carbon::createFromTimestamp($value);
        }

        // Date string / Localized date string
        if (is_string($value) && !empty($value)) {
            $timestamp = \Ems_Date_Helper::get_timestamp(get_option("date_format"), $value);
            if (!empty($timestamp)) {
                return Carbon::createFromTimestamp($timestamp);
            }
        }

        return null;
    }

    public static function toTimestamp($value): ?int
    {
        if (is_numeric($value)) {
            return (int)$value;
        }

        if ($value instanceof \DateTime) {
            return $value->getTimestamp();
        }

        return null;
    }

    public static function toDateTimePeriod($value1, $value2): \Ems_Date_Period
    {
        $value1 = static::toDateTime($value1);
        $value2 = static::toDateTime($value2);

        return new \Ems_Date_Period($value1, $value2);
    }
}

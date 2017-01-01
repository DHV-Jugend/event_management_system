<?php
namespace BIT\EMS\Schedule\Base;

/**
 * @author Christoph Bessei
 * @version
 */
abstract class Base
{
    const HOURLY = 'hourly';
    const DAILY = 'daily';

    /**
     * Recurrence parameter of wp_schedule_event. Default is 'daily'
     * @var string
     */
    protected $recurrence;

    /**
     * @var array
     */
    protected static $additionalIntervals = [];

    public function __construct()
    {
        add_filter('cron_schedules', [static::class, 'addIntervals']);
        $this->recurrence = static::DAILY;
    }

    abstract public function run();

    /**
     *
     */
    public function register()
    {
        wp_schedule_event(time(), $this->recurrence, [$this, 'run']);
    }

    public static function addIntervals($schedules)
    {
        foreach (static::$additionalIntervals as $name => $additionalInterval) {
            $schedules[$name] = array(
                'interval' => $additionalInterval['interval'],
                'display' => $additionalInterval['display']
            );
        }
        return $schedules;
    }
}
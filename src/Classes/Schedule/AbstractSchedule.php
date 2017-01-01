<?php
namespace BIT\EMS\Schedule;

/**
 * @author Christoph Bessei
 * @version
 */
abstract class AbstractSchedule
{
    const RECURRENCE_HOURLY = 'hourly';
    const RECURRENCE_DAILY = 'daily';

    /**
     * Recurrence parameter of wp_schedule_event. Default is RECURRENCE_DAILY
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
        if (empty($this->recurrence)) {
            $this->recurrence = static::RECURRENCE_DAILY;
        }
    }

    abstract public function run();

    /**
     *
     */
    public function register()
    {
        $hookName = \Ems_Conf::PREFIX . sha1(get_class());
        if (!wp_next_scheduled($hookName)) {
            wp_schedule_event(time(), $this->recurrence, $hookName);
        }
        add_action($hookName, [$this, 'run']);
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
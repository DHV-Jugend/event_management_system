<?php
namespace BIT\EMS\Service\Event;

use BIT\EMS\Domain\Model\Enum\EventsActiveUntilEnum;
use BIT\EMS\Domain\Repository\EventRepository;
use BIT\EMS\Settings\Settings;
use BIT\EMS\Settings\Tab\BasicTab;
use BIT\EMS\Utility\DateTimeUtility;
use Carbon\Carbon;
use Ems_Date_Period;
use Ems_Event;

/**
 * @author Christoph Bessei
 */
class EventService
{
    /**
     * @var \BIT\EMS\Domain\Repository\EventRepository
     */
    protected $eventRepository;

    protected $activeEventsStartDate;
    protected $activeEventsEndDate;

    public function __construct()
    {
        $this->eventRepository = new EventRepository();
        $dateFormat = get_option('date_format');

        $activeEventsStartDate = Settings::get(BasicTab::EVENT_START_DATE, BasicTab::class);
        if (!empty($activeEventsStartDate)) {
            $this->activeEventsStartDate = \Ems_Date_Helper::buildDateTime($dateFormat, $activeEventsStartDate);
        }

        $activeEventsEndDate = Settings::get(BasicTab::EVENT_END_DATE, BasicTab::class);
        if (!empty($activeEventsEndDate)) {
            $this->activeEventsEndDate = \Ems_Date_Helper::buildDateTime($dateFormat, $activeEventsEndDate);
        }
    }

    /**
     * @return \Ems_Event[]
     */
    public function findActiveEvents(): array
    {
        $allowedStartDatePeriod = null;
        $allowedEndDatePeriod = null;
        $currentDateTime = new \DateTime();

        $hideIfInPast = Settings::get(BasicTab::EVENT_ACTIVE_UNTIL, BasicTab::class);
        $eventsAreActiveUntil = Settings::get(BasicTab::EVENT_ACTIVE_UNTIL, BasicTab::class);
        switch ($eventsAreActiveUntil) {
            case EventsActiveUntilEnum::START_DATE:
                $allowedStartDatePeriod = new Ems_Date_Period(
                    $this->activeEventsStartDate,
                    $this->activeEventsEndDate
                );

                if ($hideIfInPast) {
                    $allowedEndDatePeriod = new Ems_Date_Period(new \DateTime(), $this->activeEventsEndDate);
                }
                break;
            case EventsActiveUntilEnum::END_DATE:
                if ($hideIfInPast && $currentDateTime > $this->activeEventsStartDate) {
                    $allowedEndDatePeriod = new Ems_Date_Period(new \DateTime(), $this->activeEventsEndDate);
                } else {
                    $allowedEndDatePeriod = new Ems_Date_Period(
                        $this->activeEventsStartDate, $this->activeEventsEndDate
                    );
                }
                break;
        }

        $allowedEndDatePeriod = null;

        return $this->eventRepository->findEventsInPeriod($allowedStartDatePeriod, $allowedEndDatePeriod);
    }

    /**
     * @return array
     */
    public function determineCurrentListViewEvents(): array
    {
        $events = [];
        try {
            $allowed_event_start_date_period = null;
            $allowed_event_end_date_period = null;

            $startDatePeriod = DateTimeUtility::toDateTime(BasicTab::get(BasicTab::EVENT_START_DATE));
            $endDatePeriod = DateTimeUtility::toDateTime(BasicTab::get(BasicTab::EVENT_END_DATE));

            if (empty($startDatePeriod) || empty($endDatePeriod)) {
                //  event period is necessary, hide all events to avoid unwanted disclosure of events.
                $events = [];
            } else {
                $allowed_event_start_date_period = DateTimeUtility::toDateTimePeriod($startDatePeriod, $endDatePeriod);

                // TODO: Respect BasicTab::EVENT_ACTIVE_UNTIL
                if (BasicTab::get(BasicTab::EVENT_HIDE_IF_IN_PAST)) {
                    // Only events with end date >= today are shown
                    $allowed_event_end_date_period = new \Ems_Date_Period(
                        new \DateTime(), Carbon::createFromDate(9999)
                    );
                }

                $events = Ems_Event::get_events(
                    -1,
                    true,
                    false,
                    null,
                    [],
                    $allowed_event_start_date_period,
                    $allowed_event_end_date_period
                );
            }
        } catch (\Throwable $e) {
            error_log($e->getMessage());
        }

        return $events;
    }
}

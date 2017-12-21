<?php
namespace BIT\EMS\Service\Event;

use BIT\EMS\Domain\Model\Enum\EventsActiveUntilEnum;
use BIT\EMS\Domain\Repository\EventRepository;
use BIT\EMS\Settings\Settings;
use BIT\EMS\Settings\Tab\BasicTab;
use Ems_Date_Period;

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
}

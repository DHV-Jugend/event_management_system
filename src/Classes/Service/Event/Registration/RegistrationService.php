<?php
namespace BIT\EMS\Service\Event\Registration;

use BIT\EMS\Domain\Model\EventRegistration;
use BIT\EMS\Domain\Repository\EventRegistrationRepository;
use BIT\EMS\Domain\Repository\EventRepository;
use BIT\EMS\Exception\Event\EventRegistrationAlreadyExists;
use BIT\EMS\Exception\Event\EventRegistrationNotFoundException;
use BIT\EMS\Log\EventRegistrationLog;
use BIT\EMS\Settings\Tab\BasicTab;
use Carbon\Carbon;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

/**
 * @author Christoph Bessei
 */
class RegistrationService
{
    /**
     * @var \BIT\EMS\Domain\Repository\EventRepository
     */
    protected $eventRepository;

    /**
     * @var  \BIT\EMS\Domain\Repository\EventRegistrationRepository
     */
    protected $eventRegistrationRepository;

    /**
     * @var  \BIT\EMS\Log\EventRegistrationLog
     */
    protected $eventRegistrationLog;

    /**
     * @var \BIT\EMS\Service\Event\Registration\MailService
     */
    protected $mailService;

    public function __construct()
    {
        $this->eventRepository = new EventRepository();
        $this->eventRegistrationRepository = new EventRegistrationRepository();
        $this->eventRegistrationLog = new EventRegistrationLog();
        $this->mailService = new MailService();
    }

    public function removeByParticipantAndEvent(int $event, int $participant)
    {
        $eventRegistration = (new EventRegistration())->setEventId($event)->setUserId($participant);
        return $this->removeByEventRegistration($eventRegistration);
    }

    /**
     * @param EventRegistration $eventRegistration
     * @return bool
     */
    public function removeByEventRegistration(EventRegistration $eventRegistration)
    {
        try {
            $this->eventRegistrationRepository->removeByEventRegistration($eventRegistration);
            $this->eventRegistrationLog->info('Deleted event registration.', $eventRegistration);
            $this->eventRegistrationLog->info('Sent delete event registration mail.', $eventRegistration);
            $this->mailService->sendCancelMail($eventRegistration);
            return true;
        } catch (\Throwable $e) {
            return false;
        }
    }

    /**
     * @param \BIT\EMS\Domain\Model\EventRegistration $eventRegistration
     * @throws \BIT\EMS\Exception\Event\EventRegistrationAlreadyExists
     * @throws \Exception
     */
    public function add(EventRegistration $eventRegistration)
    {
        try {
            $this->eventRegistrationRepository->add($eventRegistration);
            $this->eventRegistrationLog->info('Added event registration.', $eventRegistration);
            $this->mailService->sendRegisterMail($eventRegistration);
            $this->eventRegistrationLog->info('Sent add event registration mail.', $eventRegistration);
        } catch (UniqueConstraintViolationException $e) {
            throw new EventRegistrationAlreadyExists("User is already registered for this event");
        }
    }

    public function isRegistered(int $eventId, int $participantId)
    {
        try {
            $this->eventRegistrationRepository->findByEventAndParticipant($eventId, $participantId);
            return true;
        } catch (EventRegistrationNotFoundException $e) {
            return false;
        }
    }

    public function canParticipantCancelRegistration($event): bool
    {
        if (!$event instanceof \Ems_Event && is_numeric($event)) {
            $event = $this->eventRepository->findEventById($event);
        }

        // Can't cancel registration of invalid event
        if (is_null($event)) {
            return false;
        }

        switch (BasicTab::get(BasicTab::EVENT_ALLOW_CANCEL_REGISTRATION_UNTIL)) {
            case 'always':
                return true;
                break;
            case 'event_start':
                if (is_null($event->get_start_date_time())) {
                    // Comparison not possible
                    return false;
                }

                $today = Carbon::create()->startOfDay();
                $startDateTime = Carbon::instance($event->get_start_date_time())->startOfDay();

                if ($today <= $startDateTime) {
                    return true;
                }
                break;
            case 'event_end':
                if (is_null($event->get_end_date_time())) {
                    // Comparison not possible
                    return false;
                }

                $today = Carbon::create()->startOfDay();
                $endDateTime = Carbon::instance($event->get_end_date_time())->startOfDay();

                if ($today <= $endDateTime) {
                    return true;
                }
                break;

        }

        return false;
    }
}

<?php
namespace BIT\EMS\Service\Event\Registration;

use BIT\EMS\Domain\Model\EventRegistration;
use BIT\EMS\Domain\Repository\EventRegistrationRepository;
use BIT\EMS\Exception\Event\EventRegistrationAlreadyExists;
use BIT\EMS\Exception\Event\EventRegistrationNotFoundException;
use BIT\EMS\Log\EventRegistrationLog;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;

/**
 * @author Christoph Bessei
 */
class RegistrationService
{
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

    public function update(EventRegistration $eventRegistration)
    {
        // TODO: Implement
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
}

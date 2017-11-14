<?php
namespace BIT\EMS\Service\Event\Registration;

use BIT\EMS\Domain\Repository\EventRegistrationRepository;
use BIT\EMS\Exception\Event\EventRegistrationAlreadyExists;
use BIT\EMS\Exception\Event\EventRegistrationNotFoundException;
use BIT\EMS\Log\EventRegistrationLog;
use Ems_Event_Registration;

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
        try {
            $eventRegistration = $this->eventRegistrationRepository->findByEventAndParticipant($event, $participant);
            $this->removeByEventRegistration($eventRegistration);
        } catch (EventRegistrationNotFoundException $e) {
            return false;
        }

        return true;
    }

    /**
     * @param \Ems_Event_Registration $eventRegistration
     * @throws \Exception
     */
    public function removeByEventRegistration(Ems_Event_Registration $eventRegistration)
    {
        if ($this->eventRegistrationRepository->removeByEventRegistration($eventRegistration)) {
            $this->eventRegistrationLog->info('Deleted event registration.', $eventRegistration);
            $this->eventRegistrationLog->info('Sent delete event registration mail.', $eventRegistration);
            $this->mailService->sendCancelMail($eventRegistration);
        } else {
            $this->eventRegistrationLog->error('removeByEventRegistration failed', $eventRegistration);
        }
    }

    public function addEventRegistration(Ems_Event_Registration $eventRegistration)
    {
        if (Ems_Event_Registration::is_already_registered($eventRegistration)) {
            throw new EventRegistrationAlreadyExists("User is already registered for this event");
        }

        if ($this->eventRegistrationRepository->add($eventRegistration)) {
            $this->eventRegistrationLog->info('Added event registration.', $eventRegistration);
            $this->mailService->sendRegisterMail($eventRegistration);
            $this->eventRegistrationLog->info('Sent add event registration mail.', $eventRegistration);
        } else {
            $this->eventRegistrationLog->error("Couldn't add event registration.", $eventRegistration);
        }
    }
}

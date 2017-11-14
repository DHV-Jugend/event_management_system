<?php
/**
 * @author Christoph Bessei
 */

namespace BIT\EMS\Domain\Repository;


use BIT\EMS\Domain\Mapper\EventRegistrationMapper;
use BIT\EMS\Domain\Model\EventRegistration;
use BIT\EMS\Exception\Event\EventRegistrationNotFoundException;
use BIT\EMS\Log\EventRegistrationLog;

class EventRegistrationRepository extends AbstractDatabaseRepository
{
    protected $tableWithoutPrefix = \Ems_Conf::PREFIX . 'event_registration';

    /**
     * @var \BIT\EMS\Log\EventRegistrationLog
     */
    protected $eventRegistrationLog;

    /**
     * @var \BIT\EMS\Domain\Mapper\EventRegistrationMapper
     */
    protected $eventRegistrationMapper;

    public function __construct()
    {
        parent::__construct();
        $this->eventRegistrationLog = new EventRegistrationLog();
        $this->eventRegistrationMapper = new EventRegistrationMapper();
    }

    /**
     * @param array $fields
     * @return EventRegistration[]
     */
    public function findAll(array $fields = ['*']): array
    {
        $entries = parent::findByIdentifier(['deleted' => false]);
        return $this->eventRegistrationMapper->toObject($entries);
    }

    /**
     * @param \Ems_Event $event
     * @return EventRegistration[]
     */
    public function findByEvent(\Ems_Event $event): array
    {
        $entries = $this->findByIdentifier(['event_id' => $event->getID(), 'deleted' => false]);
        return $this->eventRegistrationMapper->toObject($entries);
    }

    /**
     * @param int $user_id
     * @return EventRegistration[]
     */
    public function findByParticipant(int $user_id): array
    {
        $entries = $this->findByIdentifier(['user_id' => $user_id, 'deleted' => false]);
        return $this->eventRegistrationMapper->toObject($entries);
    }

    public function findByEventAndParticipant(int $eventId, int $participantId): EventRegistration
    {
        $entries = $this->findByIdentifier(['event_id' => $eventId, 'user_id' => $participantId, 'deleted' => false]);
        if (empty($entries)) {
            throw new EventRegistrationNotFoundException('Event: ' . $eventId . ' User: ' . $participantId);
        }
        $entry = reset($entries);
        return $this->eventRegistrationMapper->toSingleObject($entry);
    }

    /**
     * @param \BIT\EMS\Domain\Model\EventRegistration $eventRegistration
     * @throws \Doctrine\DBAL\Exception\UniqueConstraintViolationException
     */
    public function add(EventRegistration $eventRegistration)
    {
        $entry = $this->eventRegistrationMapper->toSingleArray($eventRegistration);

        $identifier = $entry;
        unset($identifier['data']);

        // Check if exists with deleted = true
        $dbEntry = $this->findByIdentifier(array_merge($identifier, ['deleted' => true]));
        if (!empty($dbEntry)) {
            // Set deleted to false
            $this->update(['deleted' => false], $identifier);
        } else {
            $this->insert($entry);
        }
    }

    public function removeByEventRegistration(EventRegistration $registration)
    {
        $this->removeByEventAndParticipant($registration->getEventId(), $registration->getUserId());
    }

    public function removeByEventAndParticipant(int $eventId, int $participantId)
    {
        $this->delete(['event_id' => $eventId, 'user_id' => $participantId]);
    }

    public function delete(array $identifier)
    {
        $this->update(['deleted' => true], $identifier);
    }
}

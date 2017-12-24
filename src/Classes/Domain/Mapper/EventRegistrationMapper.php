<?php
namespace BIT\EMS\Domain\Mapper;

use BIT\EMS\Domain\Model\EventRegistration;

/**
 * @author Christoph Bessei
 */
class EventRegistrationMapper implements MapperInterface
{
    /**
     * @param array $entries
     * @return EventRegistration[]
     */
    public function toObject(array $entries): array
    {
        $mappedEntries = [];
        foreach ($entries as $key => $entry) {
            $mappedEntries[$key] = $this->toSingleObject($entry);
        }
        return $mappedEntries;
    }

    /**
     * @param array $entry
     * @return \BIT\EMS\Domain\Model\EventRegistration
     */
    public function toSingleObject(array $entry)
    {
        $eventRegistration = new EventRegistration();
        $eventRegistration
            ->setEventId((int)$entry['event_id'])
            ->setUserId((int)$entry['user_id']);

        if (!empty($entry['data'])) {
            // Fix json_
            $data = json_decode(utf8_encode($entry['data']), JSON_OBJECT_AS_ARRAY);
            if (!is_array($data)) {
                error_log(
                    "Registration data isn't an array: " . var_export(
                        $data,
                        true
                    ) . ' Event: ' . $eventRegistration->getEventId() . ' User:' . $eventRegistration->getUserId()
                );
                error_log('JSON error: ' . json_last_error() . ' ' . json_last_error_msg());
                $data = [];
            }
            $eventRegistration->setData($data);
        }

        return $eventRegistration;
    }

    /**
     * @param EventRegistration[] $objects
     * @return array
     */
    public function toArray(array $objects): array
    {
        $mappedEntries = [];
        foreach ($objects as $key => $object) {
            $mappedEntries[$key] = $this->toSingleArray($object);
        }
        return $mappedEntries;
    }

    /**
     * @param \BIT\EMS\Domain\Model\EventRegistration $object
     * @return array
     */
    public function toSingleArray($object): array
    {
        return [
            'user_id' => $object->getUserId(),
            'event_id' => $object->getEventId(),
            'data' => json_encode($object->getData()),
        ];
    }
}

<?php
namespace BIT\EMS\Domain\Mapper;

/**
 * @author Christoph Bessei
 */
abstract class AbstractMapper implements MapperInterface
{
    public function toObject(array $entries): array
    {
        $objects = [];
        foreach ($entries as $key => $entry) {
            $objects[$key] = $this->toSingleObject($entry);
        }
        return $objects;
    }

    public function toArray(array $objects): array
    {
        $arrays = [];
        foreach ($objects as $key => $object) {
            $arrays[$key] = $this->toSingleArray($object);
        }
        return $arrays;
    }
}

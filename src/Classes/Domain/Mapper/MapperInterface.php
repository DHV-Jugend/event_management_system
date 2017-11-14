<?php
namespace BIT\EMS\Domain\Mapper;

/**
 * @author Christoph Bessei
 */
interface MapperInterface
{
    public function toObject(array $entries): array;

    public function toSingleObject(array $entries);

    public function toArray(array $objects): array;

    public function toSingleArray($object): array;
}

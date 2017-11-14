<?php
namespace BIT\EMS\Domain\Model;

/**
 * @author Christoph Bessei
 */
interface EqualsInterface
{
    public function equals(self $otherObject): bool;
}

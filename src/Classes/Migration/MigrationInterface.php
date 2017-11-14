<?php
namespace BIT\EMS\Migration;

/**
 * @author Christoph Bessei
 */
interface MigrationInterface
{
    public function run();

    public function getResultMessage(): string;

    public function getDescription(): string;
}

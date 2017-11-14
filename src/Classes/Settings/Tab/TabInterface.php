<?php
/**
 * @author Christoph Bessei
 */
namespace BIT\EMS\Settings\Tab;

interface TabInterface
{
    public function getId(): string;

    public function getTitle(): string;

    public function getFields(): array;
}
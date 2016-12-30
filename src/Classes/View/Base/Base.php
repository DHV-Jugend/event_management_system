<?php

namespace BIT\EMS\View\Base;

/**
 * @author Christoph Bessei
 * @version
 */
abstract class Base
{
    /**
     * @var array
     */
    protected $arguments;

    public function __construct(array $arguments = [])
    {
        $this->arguments = $arguments;
    }

    abstract public function printContent();
}
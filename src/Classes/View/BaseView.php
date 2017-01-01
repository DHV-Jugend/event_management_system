<?php

namespace BIT\EMS\View;

/**
 * @author Christoph Bessei
 * @version
 */
abstract class BaseView
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
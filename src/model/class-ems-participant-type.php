<?php

/**
 * @author Christoph Bessei
 * @version
 */
class Ems_Participant_Type
{
    /** @var  string */
    protected $label;
    /** @var  bool */
    protected $value;
    /** @var  string */
    protected $key;

    /**
     * Ems_Participant_Level constructor.
     * @param string $label
     * @param bool $value
     * @param string $key
     */
    public function __construct($label, $value, $key)
    {
        $this->label = $label;
        $this->value = boolval($value);
        $this->key = $key;
    }

    /**
     * @return bool
     */
    public function getValue()
    {
        return $this->value;
    }

    /**
     * @param bool $value
     */
    public function setValue($value)
    {
        $this->value = $value;
    }

    /**
     * @return string
     */
    public function getLabel()
    {
        return $this->label;
    }

    /**
     * @param string $label
     */
    public function setLabel($label)
    {
        $this->label = $label;
    }

    /**
     * @return string
     */
    public function getKey()
    {
        return $this->key;
    }

    /**
     * @param string $key
     */
    public function setKey($key)
    {
        $this->key = $key;
    }
}
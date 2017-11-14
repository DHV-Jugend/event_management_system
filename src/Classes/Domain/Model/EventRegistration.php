<?php
/**
 * @author Christoph Bessei
 */

namespace BIT\EMS\Domain\Model;

class EventRegistration
{
    /**
     * @var int
     */
    protected $event_id;

    /**
     * @var int
     */
    protected $user_id;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @return int
     */
    public function getEventId()
    {
        return $this->event_id;
    }

    /**
     * @param int $event_id
     * @return $this
     */
    public function setEventId(int $event_id)
    {
        $this->event_id = $event_id;
        return $this;
    }


    /**
     * @return int
     */
    public function getUserId(): int
    {
        return $this->user_id;
    }

    /**
     * @param int $user_id
     * @return $this
     */
    public function setUserId($user_id)
    {
        $this->user_id = $user_id;
        return $this;
    }

    /**
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    /**
     * @param array $data
     * @return $this
     */
    public function setData(array $data)
    {
        $this->data = $data;
        return $this;
    }
}
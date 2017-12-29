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
    protected $id;

    /**
     * @var int
     */
    protected $event_id;

    /**
     * @var int
     */
    protected $user_id;

    /**
     * IMPORTANT: This column is not immutable! It will be updated on changes of the deleted column from 1 to 0.
     *
     * @var \DateTime|null
     */
    protected $create_date;

    /**
     * @var \DateTime|null
     */
    protected $modify_date;

    /**
     * IMPORTANT: This column is not immutable! It will be updated each time the deleted column changes from 0 to 1.
     *
     * @var \DateTime|null
     */
    protected $delete_date;

    /**
     * @var array
     */
    protected $data = [];

    /**
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @param int $id
     * @return $this
     */
    public function setId(int $id)
    {
        $this->id = $id;
        return $this;
    }

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
    public function setUserId(int $user_id)
    {
        $this->user_id = $user_id;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getCreateDate(): ?\DateTime
    {
        return $this->create_date;
    }

    /**
     * @param \DateTime|null $create_date
     * @return $this
     */
    public function setCreateDate(?\DateTime $create_date)
    {
        $this->create_date = $create_date;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getModifyDate(): ?\DateTime
    {
        return $this->modify_date;
    }

    /**
     * @param \DateTime|null $modify_date
     * @return $this
     */
    public function setModifyDate(?\DateTime $modify_date)
    {
        $this->modify_date = $modify_date;
        return $this;
    }

    /**
     * @return \DateTime|null
     */
    public function getDeleteDate(): ?\DateTime
    {
        return $this->delete_date;
    }

    /**
     * @param \DateTime|null $delete_date
     * @return $this
     */
    public function setDeleteDate(?\DateTime $delete_date)
    {
        $this->delete_date = $delete_date;
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
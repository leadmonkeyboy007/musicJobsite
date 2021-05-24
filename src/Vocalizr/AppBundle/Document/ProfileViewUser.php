<?php

namespace Vocalizr\AppBundle\Document;

use Doctrine\ODM\MongoDB\Mapping\Annotations as MongoDB;

/**
 * @MongoDB\Document(repositoryClass="Vocalizr\AppBundle\Repository\ProfileViewUserRepository")
 */
class ProfileViewUser
{
    /**
     * @MongoDB\Id
     */
    protected $id;

    /**
     * @MongoDB\String
     */
    protected $date;

    /**
     * @MongoDB\String
     */
    protected $created_at;

    /**
     * @MongoDB\Int
     * @MongoDB\Index
     */
    protected $user_id;

    /**
     * @MongoDB\Int
     */
    protected $from_user_id;

    public function fromArray($data)
    {
        foreach ($data as $key => $value) {
            $this->$key = $value;
        }
    }

    /**
     * Get id
     *
     * @return id $id
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set date
     *
     * @param date $date
     *
     * @return self
     */
    public function setDate($date)
    {
        $this->date = $date;
        return $this;
    }

    /**
     * Get date
     *
     * @return date $date
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set userId
     *
     * @param int $userId
     *
     * @return self
     */
    public function setUserId($userId)
    {
        $this->user_id = $userId;
        return $this;
    }

    /**
     * Get userId
     *
     * @return int $userId
     */
    public function getUserId()
    {
        return $this->user_id;
    }

    /**
     * Set fromUserId
     *
     * @param int $fromUserId
     *
     * @return self
     */
    public function setFromUserId($fromUserId)
    {
        $this->from_user_id = $fromUserId;
        return $this;
    }

    /**
     * Get fromUserId
     *
     * @return int $fromUserId
     */
    public function getFromUserId()
    {
        return $this->from_user_id;
    }

    /**
     * Set createdAt
     *
     * @param string $createdAt
     *
     * @return self
     */
    public function setCreatedAt($createdAt)
    {
        $this->created_at = $createdAt;
        return $this;
    }

    /**
     * Get createdAt
     *
     * @return string $createdAt
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }
}
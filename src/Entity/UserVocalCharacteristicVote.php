<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\UserVocalCharacteristicVoteRepository")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(name="user_vocal_characteristic_vote")
 */
class UserVocalCharacteristicVote
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    public $id;

    /**
     * @ORM\ManyToOne(targetEntity="UserInfo")
     */
    protected $from_user_info;

    /**
     * @ORM\ManyToOne(targetEntity="UserVocalCharacteristic", inversedBy="user_vocal_characteristic_votes")
     */
    protected $user_vocal_characteristic;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created_at;

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->created_at = new \DateTime();
    }

    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     *
     * @return UserVocalCharacteristicVote
     */
    public function setCreatedAt($createdAt)
    {
        $this->created_at = $createdAt;

        return $this;
    }

    /**
     * Get created_at
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->created_at;
    }

    /**
     * Set from_user_info
     *
     * @param \App\Entity\UserInfo $fromUserInfo
     *
     * @return UserVocalCharacteristicVote
     */
    public function setFromUserInfo(\App\Entity\UserInfo $fromUserInfo = null)
    {
        $this->from_user_info = $fromUserInfo;

        return $this;
    }

    /**
     * Get from_user_info
     *
     * @return \App\Entity\UserInfo
     */
    public function getFromUserInfo()
    {
        return $this->from_user_info;
    }

    /**
     * Set user_vocal_characteristic
     *
     * @param \App\Entity\UserVocalCharacteristic $userVocalCharacteristic
     *
     * @return UserVocalCharacteristicVote
     */
    public function setUserVocalCharacteristic(\App\Entity\UserVocalCharacteristic $userVocalCharacteristic = null)
    {
        $this->user_vocal_characteristic = $userVocalCharacteristic;

        return $this;
    }

    /**
     * Get user_vocal_characteristic
     *
     * @return \App\Entity\UserVocalCharacteristic
     */
    public function getUserVocalCharacteristic()
    {
        return $this->user_vocal_characteristic;
    }
}
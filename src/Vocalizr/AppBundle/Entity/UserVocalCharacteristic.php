<?php

namespace Vocalizr\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Vocalizr\AppBundle\Repository\UserVocalCharacteristicRepository")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(name="user_vocal_characteristic")
 */
class UserVocalCharacteristic
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    public $id;

    /**
     * @ORM\ManyToOne(targetEntity="UserInfo", inversedBy="user_vocal_characteristics")
     */
    protected $user_info;

    /**
     * @ORM\ManyToOne(targetEntity="VocalCharacteristic", inversedBy="user_vocal_characteristics")
     */
    protected $vocal_characteristic;

    /**
     * @ORM\Column(type="integer")
     */
    protected $agree = 0;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created_at;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $updated_at;

    /**
     * @ORM\OneToMany(targetEntity="UserVocalCharacteristicVote", mappedBy="user_vocal_characteristic", cascade={"remove"})
     */
    protected $user_vocal_characteristic_votes;

    /**
     * @ORM\PrePersist
     */
    public function prePersist()
    {
        $this->created_at = new \DateTime();
    }

    /**
     * @ORM\PreUpdate
     */
    public function preUpdate()
    {
        $this->updated_at = new \DateTime();
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
     * Set agree
     *
     * @param int $agree
     *
     * @return UserVocalCharacteristic
     */
    public function setAgree($agree)
    {
        $this->agree = $agree;

        return $this;
    }

    /**
     * Get agree
     *
     * @return int
     */
    public function getAgree()
    {
        return $this->agree;
    }

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     *
     * @return UserVocalCharacteristic
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
     * Set updated_at
     *
     * @param \DateTime $updatedAt
     *
     * @return UserVocalCharacteristic
     */
    public function setUpdatedAt($updatedAt)
    {
        $this->updated_at = $updatedAt;

        return $this;
    }

    /**
     * Get updated_at
     *
     * @return \DateTime
     */
    public function getUpdatedAt()
    {
        return $this->updated_at;
    }

    /**
     * Set user_info
     *
     * @param \Vocalizr\AppBundle\Entity\UserInfo $userInfo
     *
     * @return UserVocalCharacteristic
     */
    public function setUserInfo(\Vocalizr\AppBundle\Entity\UserInfo $userInfo = null)
    {
        $this->user_info = $userInfo;

        return $this;
    }

    /**
     * Get user_info
     *
     * @return \Vocalizr\AppBundle\Entity\UserInfo
     */
    public function getUserInfo()
    {
        return $this->user_info;
    }

    /**
     * Set vocal_characteristic
     *
     * @param \Vocalizr\AppBundle\Entity\VocalCharacteristic $vocalCharacteristic
     *
     * @return UserVocalCharacteristic
     */
    public function setVocalCharacteristic(\Vocalizr\AppBundle\Entity\VocalCharacteristic $vocalCharacteristic = null)
    {
        $this->vocal_characteristic = $vocalCharacteristic;

        return $this;
    }

    /**
     * Get vocal_characteristic
     *
     * @return \Vocalizr\AppBundle\Entity\VocalCharacteristic
     */
    public function getVocalCharacteristic()
    {
        return $this->vocal_characteristic;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->user_vocal_characteristic_votes = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Add user_vocal_characteristic_votes
     *
     * @param \Vocalizr\AppBundle\Entity\UserVocalCharacteristicVote $userVocalCharacteristicVotes
     *
     * @return UserVocalCharacteristic
     */
    public function addUserVocalCharacteristicVote(\Vocalizr\AppBundle\Entity\UserVocalCharacteristicVote $userVocalCharacteristicVotes)
    {
        $this->user_vocal_characteristic_votes[] = $userVocalCharacteristicVotes;

        return $this;
    }

    /**
     * Remove user_vocal_characteristic_votes
     *
     * @param \Vocalizr\AppBundle\Entity\UserVocalCharacteristicVote $userVocalCharacteristicVotes
     */
    public function removeUserVocalCharacteristicVote(\Vocalizr\AppBundle\Entity\UserVocalCharacteristicVote $userVocalCharacteristicVotes)
    {
        $this->user_vocal_characteristic_votes->removeElement($userVocalCharacteristicVotes);
    }

    /**
     * Get user_vocal_characteristic_votes
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getUserVocalCharacteristicVotes()
    {
        return $this->user_vocal_characteristic_votes;
    }
}
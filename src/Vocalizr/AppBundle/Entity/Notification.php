<?php

namespace Vocalizr\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Vocalizr\AppBundle\Repository\NotificationRepository")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(name="notification")
 */
class Notification
{
    const NOTIFY_TYPE_LIKE = 'like';

    const NOTIFY_TYPE_PROJECT_INVITE = 'project_invite';

    const NOTIFY_TYPE_PROJECT_HIRE = 'project_hire';

    const NOTIFY_TYPE_CONNNECT_INVITE = 'connect_invite';

    const NOTIFY_TYPE_CONNNECT_ACCEPT = 'connect_accept';

    const NOTIFY_TYPE_WALLET_DEPOSIT_FAILED = 'wallet_deposit_failed';

    public static $hiddenNotifications = [
        self::NOTIFY_TYPE_WALLET_DEPOSIT_FAILED,
    ];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    public $id;

    /**
     * @ORM\ManyToOne(targetEntity="UserInfo")
     */
    protected $user_info;

    /**
     * @ORM\ManyToOne(targetEntity="UserInfo")
     */
    protected $actioned_user_info = null;

    /**
     * @ORM\ManyToOne(targetEntity="Project")
     */
    protected $project = null;

    /**
     * @ORM\ManyToOne(targetEntity="UserAudio")
     */
    protected $user_audio = null;

    /**
     * @ORM\Column(type="string", length=32, nullable=true)
     */
    protected $notify_type;

    /**
     * JSON data
     *
     * @ORM\Column(type="text", nullable=true)
     */
    protected $data = null;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created_at;

    /**
     * If the user has read this item
     *
     * @ORM\Column(type="boolean")
     */
    protected $notify_read = false;

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
     * Set notify_type
     *
     * @param string $notifyType
     *
     * @return Notification
     */
    public function setNotifyType($notifyType)
    {
        $this->notify_type = $notifyType;

        return $this;
    }

    /**
     * Get notify_type
     *
     * @return string
     */
    public function getNotifyType()
    {
        return $this->notify_type;
    }

    /**
     * Set data
     *
     * @param string $data
     *
     * @return Notification
     */
    public function setData($data)
    {
        $this->data = $data;

        return $this;
    }

    /**
     * Get data
     *
     * @return string
     */
    public function getData()
    {
        return $this->data;
    }

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     *
     * @return Notification
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
     * Set notify_read
     *
     * @param bool $notifyRead
     *
     * @return Notification
     */
    public function setNotifyRead($notifyRead)
    {
        $this->notify_read = $notifyRead;

        return $this;
    }

    /**
     * Get notify_read
     *
     * @return bool
     */
    public function getNotifyRead()
    {
        return $this->notify_read;
    }

    /**
     * Set user_info
     *
     * @param \Vocalizr\AppBundle\Entity\UserInfo $userInfo
     *
     * @return Notification
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
     * Set actioned_user_info
     *
     * @param \Vocalizr\AppBundle\Entity\UserInfo $actionedUserInfo
     *
     * @return Notification
     */
    public function setActionedUserInfo(\Vocalizr\AppBundle\Entity\UserInfo $actionedUserInfo = null)
    {
        $this->actioned_user_info = $actionedUserInfo;

        return $this;
    }

    /**
     * Get actioned_user_info
     *
     * @return \Vocalizr\AppBundle\Entity\UserInfo
     */
    public function getActionedUserInfo()
    {
        return $this->actioned_user_info;
    }

    /**
     * Set project
     *
     * @param \Vocalizr\AppBundle\Entity\Project $project
     *
     * @return Notification
     */
    public function setProject(\Vocalizr\AppBundle\Entity\Project $project = null)
    {
        $this->project = $project;

        return $this;
    }

    /**
     * Get project
     *
     * @return \Vocalizr\AppBundle\Entity\Project
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * Set user_audio
     *
     * @param \Vocalizr\AppBundle\Entity\UserAudio $userAudio
     *
     * @return Notification
     */
    public function setUserAudio(\Vocalizr\AppBundle\Entity\UserAudio $userAudio = null)
    {
        $this->user_audio = $userAudio;

        return $this;
    }

    /**
     * Get user_audio
     *
     * @return \Vocalizr\AppBundle\Entity\UserAudio
     */
    public function getUserAudio()
    {
        return $this->user_audio;
    }
}
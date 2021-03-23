<?php

namespace Vocalizr\AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="Vocalizr\AppBundle\Repository\UserVocalStyleVoteRepository")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(name="user_vocal_style_vote")
 */
class UserVocalStyleVote
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
     * @ORM\ManyToOne(targetEntity="UserVocalStyle", inversedBy="user_vocal_style_votes")
     */
    protected $user_vocal_style;

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
     * @return UserVocalStyleVote
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
     * @param \Vocalizr\AppBundle\Entity\UserInfo $fromUserInfo
     *
     * @return UserVocalStyleVote
     */
    public function setFromUserInfo(\Vocalizr\AppBundle\Entity\UserInfo $fromUserInfo = null)
    {
        $this->from_user_info = $fromUserInfo;

        return $this;
    }

    /**
     * Get from_user_info
     *
     * @return \Vocalizr\AppBundle\Entity\UserInfo
     */
    public function getFromUserInfo()
    {
        return $this->from_user_info;
    }

    /**
     * Set user_vocal_style
     *
     * @param \Vocalizr\AppBundle\Entity\UserVocalStyle $userVocalStyle
     *
     * @return UserVocalStyleVote
     */
    public function setUserVocalStyle(\Vocalizr\AppBundle\Entity\UserVocalStyle $userVocalStyle = null)
    {
        $this->user_vocal_style = $userVocalStyle;

        return $this;
    }

    /**
     * Get user_vocal_style
     *
     * @return \Vocalizr\AppBundle\Entity\UserVocalStyle
     */
    public function getUserVocalStyle()
    {
        return $this->user_vocal_style;
    }
}
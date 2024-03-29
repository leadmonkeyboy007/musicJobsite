<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ProjectEscrowRepository")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(name="project_escrow")
 */
class ProjectEscrow
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
    protected $user_info;

    /**
     * @ORM\OneToOne(targetEntity="Project", mappedBy="project_escrow")
     */
    protected $project;

    /**
     * @ORM\ManyToOne(targetEntity="ProjectBid")
     */
    protected $project_bid;

    /**
     * In cents
     *
     * @ORM\Column(type="integer", length=11)
     */
    protected $amount = 0;

    /**
     * In cents
     *
     * @ORM\Column(type="integer", length=11)
     */
    protected $fee = 0;

    /**
     * In cents
     *
     * @ORM\Column(type="integer", length=11)
     */
    protected $contractor_fee = 0;

    /**
     * Date the payment was released
     *
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $released_date = null;

    /**
     * If escrow was refunded
     *
     * @ORM\Column(type="boolean")
     */
    protected $refunded = false;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created_at;

    /**
     * @ORM\Column(type="datetime", nullable=true)
     */
    protected $updated_at = null;

    /**
     * Relationships
     */

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
     * Get amount in dollars.
     * Converts cents to dollars
     *
     * @return float
     */
    public function getAmountDollars()
    {
        return number_format($this->amount / 100, 2, '.', ',');
    }

    /**
     * Get fee in dollars.
     * Converts cents to dollars
     *
     * @return float
     */
    public function getFeeDollars()
    {
        return number_format($this->fee / 100, 2, '.', ',');
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
     * Set amount
     *
     * @param string $amount
     *
     * @return ProjectEscrow
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount
     *
     * @return string
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set fee
     *
     * @param string $fee
     *
     * @return ProjectEscrow
     */
    public function setFee($fee)
    {
        $this->fee = $fee;

        return $this;
    }

    /**
     * Get fee
     *
     * @return string
     */
    public function getFee()
    {
        return $this->fee;
    }

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     *
     * @return ProjectEscrow
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
     * @return ProjectEscrow
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
     * @param \App\Entity\UserInfo $userInfo
     *
     * @return ProjectEscrow
     */
    public function setUserInfo(\App\Entity\UserInfo $userInfo = null)
    {
        $this->user_info = $userInfo;

        return $this;
    }

    /**
     * Get user_info
     *
     * @return \App\Entity\UserInfo
     */
    public function getUserInfo()
    {
        return $this->user_info;
    }

    /**
     * Set project_bid
     *
     * @param \App\Entity\ProjectBid $projectBid
     *
     * @return ProjectEscrow
     */
    public function setProjectBid(\App\Entity\ProjectBid $projectBid = null)
    {
        $this->project_bid = $projectBid;

        return $this;
    }

    /**
     * Get project_bid
     *
     * @return \App\Entity\ProjectBid
     */
    public function getProjectBid()
    {
        return $this->project_bid;
    }

    /**
     * Set project
     *
     * @param \App\Entity\Project $project
     *
     * @return ProjectEscrow
     */
    public function setProject(\App\Entity\Project $project = null)
    {
        $this->project = $project;

        return $this;
    }

    /**
     * Get project
     *
     * @return \App\Entity\Project
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * Set released_date
     *
     * @param \DateTime $releasedDate
     *
     * @return ProjectEscrow
     */
    public function setReleasedDate($releasedDate)
    {
        $this->released_date = $releasedDate;

        return $this;
    }

    /**
     * Get released_date
     *
     * @return \DateTime
     */
    public function getReleasedDate()
    {
        return $this->released_date;
    }

    /**
     * Set refunded
     *
     * @param bool $refunded
     *
     * @return ProjectEscrow
     */
    public function setRefunded($refunded)
    {
        $this->refunded = $refunded;

        return $this;
    }

    /**
     * Get refunded
     *
     * @return bool
     */
    public function getRefunded()
    {
        return $this->refunded;
    }

    /**
     * Set contractor_fee
     *
     * @param int $contractorFee
     *
     * @return ProjectEscrow
     */
    public function setContractorFee($contractorFee)
    {
        $this->contractor_fee = $contractorFee;

        return $this;
    }

    /**
     * Get contractor_fee
     *
     * @return int
     */
    public function getContractorFee()
    {
        return $this->contractor_fee;
    }
}
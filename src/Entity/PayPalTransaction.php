<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\PayPalTransactionRepository")
 * @ORM\HasLifecycleCallbacks()
 * @ORM\Table(name="paypal_transaction", indexes={@ORM\Index(name="admin_search_idx", columns={"user_info_id", "txn_id"})})
 */
class PayPalTransaction
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    public $id;

    /**
     * @ORM\Column(type="string", length=64)
     */
    protected $txn_type;

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    protected $ipn_track_id = null;

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    protected $txn_id = null;

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     */
    protected $subscr_id = null;

    /**
     * @ORM\Column(type="string", length=255)
     */
    protected $payer_email;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    protected $item_name = null;

    /**
     * @ORM\Column(type="decimal", precision=9, scale=3)
     */
    protected $payment_gross = 0.0;

    /**
     * @ORM\Column(type="decimal", precision=9, scale=3)
     */
    protected $amount = 0.0;

    /**
     * @ORM\Column(type="boolean")
     */
    protected $verified = false;

    /**
     * @ORM\Column(type="text")
     */
    protected $raw;

    /**
     * @ORM\Column(type="datetime")
     */
    protected $created_at;

    /**
     * @var UserInfo|null
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\UserInfo")
     * @ORM\JoinColumn(name="user_info_id", nullable=true, onDelete="SET NULL")
     */
    protected $user_info;

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
     * Set txn_type
     *
     * @param string $txnType
     *
     * @return PayPalTransaction
     */
    public function setTxnType($txnType)
    {
        $this->txn_type = $txnType;

        return $this;
    }

    /**
     * Get txn_type
     *
     * @return string
     */
    public function getTxnType()
    {
        return $this->txn_type;
    }

    /**
     * Set ipn_track_id
     *
     * @param string $ipnTrackId
     *
     * @return PayPalTransaction
     */
    public function setIpnTrackId($ipnTrackId)
    {
        $this->ipn_track_id = $ipnTrackId;

        return $this;
    }

    /**
     * Get ipn_track_id
     *
     * @return string
     */
    public function getIpnTrackId()
    {
        return $this->ipn_track_id;
    }

    /**
     * Set txn_id
     *
     * @param string $txnId
     *
     * @return PayPalTransaction
     */
    public function setTxnId($txnId)
    {
        $this->txn_id = $txnId;

        return $this;
    }

    /**
     * Get txn_id
     *
     * @return string
     */
    public function getTxnId()
    {
        return $this->txn_id;
    }

    /**
     * Set payer_email
     *
     * @param string $payerEmail
     *
     * @return PayPalTransaction
     */
    public function setPayerEmail($payerEmail)
    {
        $this->payer_email = $payerEmail;

        return $this;
    }

    /**
     * Get payer_email
     *
     * @return string
     */
    public function getPayerEmail()
    {
        return $this->payer_email;
    }

    /**
     * Set payment_gross
     *
     * @param float $paymentGross
     *
     * @return PayPalTransaction
     */
    public function setPaymentGross($paymentGross)
    {
        $this->payment_gross = $paymentGross;

        return $this;
    }

    /**
     * Get payment_gross
     *
     * @return float
     */
    public function getPaymentGross()
    {
        return $this->payment_gross;
    }

    /**
     * Set amount
     *
     * @param float $amount
     *
     * @return PayPalTransaction
     */
    public function setAmount($amount)
    {
        $this->amount = $amount;

        return $this;
    }

    /**
     * Get amount
     *
     * @return float
     */
    public function getAmount()
    {
        return $this->amount;
    }

    /**
     * Set verified
     *
     * @param bool $verified
     *
     * @return PayPalTransaction
     */
    public function setVerified($verified)
    {
        $this->verified = $verified;

        return $this;
    }

    /**
     * Get verified
     *
     * @return bool
     */
    public function getVerified()
    {
        return $this->verified;
    }

    /**
     * Set raw
     *
     * @param string $raw
     *
     * @return PayPalTransaction
     */
    public function setRaw($raw)
    {
        $this->raw = $raw;

        return $this;
    }

    /**
     * Get raw
     *
     * @return string
     */
    public function getRaw()
    {
        return $this->raw;
    }

    /**
     * Set created_at
     *
     * @param \DateTime $createdAt
     *
     * @return PayPalTransaction
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
     * Set subscr_id
     *
     * @param string $subscrId
     *
     * @return PayPalTransaction
     */
    public function setSubscrId($subscrId)
    {
        $this->subscr_id = $subscrId;

        return $this;
    }

    /**
     * Get subscr_id
     *
     * @return string
     */
    public function getSubscrId()
    {
        return $this->subscr_id;
    }

    /**
     * Set item_name
     *
     * @param string $itemName
     *
     * @return PayPalTransaction
     */
    public function setItemName($itemName)
    {
        $this->item_name = $itemName;

        return $this;
    }

    /**
     * Get item_name
     *
     * @return string
     */
    public function getItemName()
    {
        return $this->item_name;
    }

    /**
     * @return UserInfo|null
     */
    public function getUserInfo()
    {
        return $this->user_info;
    }

    /**
     * @param UserInfo|null $user_info
     * @return PayPalTransaction
     */
    public function setUserInfo($user_info)
    {
        $this->user_info = $user_info;
        return $this;
    }
}
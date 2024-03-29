<?php

namespace App\Model;

use App\Entity\HintSkip;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityRepository;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\HttpFoundation\Session\Session;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Core\Security;
use App\Entity\UserInfo;
use App\Repository\HintSkipRepository;

/**
 * Class HintModel
 *
 * @package App\Model
 */
class HintModel
{
    const HINT_BID = 1;

    const HINT_ADVANCED_BID = 2;

    const HINT_GIG = 3;

    const HINT_CONTEST = 4;

    const HINT_TARGET_GIG = 'gig';

    const HINT_TARGET_CONTEST = 'contest';

    public static $hintIds = [
        self::HINT_GIG,
        self::HINT_CONTEST,
    ];

    public static $hintTargets = [
        self::HINT_TARGET_GIG,
        self::HINT_TARGET_CONTEST,
    ];

    const VISIBILITY_VISIBLE = 'enable';

    const VISIBILITY_HIDDEN = 'disable';

    /** @var EntityManager */
    private $em;

    /** @var Session */
    private $session;

    /** @var UserInfo|null */
    private $user;

    /**
     * HintModel constructor.
     *
     * @param Session         $session
     * @param Security        $security
     * @param EntityManager   $em
     */
    public function __construct(Session $session, Security $security, EntityManager $em)
    {
        $this->em      = $em;
        $this->session = $session;
        $this->user    = $security->getUser();
    }

    /**
     * @param int      $hintType
     * @param int|null $amount
     * @param string   $target
     */
    public function setSession($hintType, $amount = null, $target = self::HINT_TARGET_GIG)
    {
        if ($this->user && !$this->user->isSubscribed() && !$this->isSkipped($hintType)) {
            $bag      = $this->session->getFlashBag();
            $hintData = [
                'type'   => $hintType,
                'amount' => $amount,
                'target' => $target,
            ];
            $bag->add('hint', json_encode($hintData));
        }
    }

    /**
     * @param int    $hintType
     * @param string $visibility
     *
     * @return bool
     */
    public function setVisibility($hintType, $visibility)
    {
        if (!$this->user) {
            return false;
        }

        if ($visibility === self::VISIBILITY_VISIBLE) {
            $this->getRepository()->removeSkip($this->user, $hintType);
        } else {
            $this->getRepository()->setSkipped($this->user, $hintType);
        }

        return true;
    }

    /**
     * @param int $hintType
     *
     * @return bool
     */
    public function isSkipped($hintType)
    {
        return $this->getRepository()->isSkipped($this->user, $hintType);
    }

    /**
     * @param string $class
     *
     * @return EntityRepository|HintSkipRepository
     */
    private function getRepository($class = HintSkip::class)
    {
        return $this->em->getRepository($class);
    }
}
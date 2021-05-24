<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;
use App\Entity\UserSubscription;

/**
 * UserSubscriptionRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class UserSubscriptionRepository extends EntityRepository
{
    /**
     * Create free user subscription for user
     *
     * @param int $userInfoId
     *
     * @return UserSubscription
     */
    public function createFreeUserSubscription($userInfoId)
    {
        // Get free membership subscription
        if (!$subscriptionPlan = $this->_em->getRepository('App:SubscriptionPlan')->findOneBy([
            'static_key' => 'FREE',
        ])) {
            return false;
        }

        // Cancel any active subscriptions
        $this->cancelActiveSubscriptions();

        $entity = new UserSubscription();
        $entity->setDateCommenced(new \DateTime());
        $entity->setIsActive(true);
        $entity->setUserInfo($this->_em->getReference('App:UserInfo', $userInfoId));
        $entity->setSubscriptionPlan($subscriptionPlan);

        $this->_em->persist($entity);
        $this->_em->flush();

        return $entity;
    }

    /**
     * Cancel any active subscriptions
     */
    public function cancelActiveSubscriptions()
    {
        $q = $this->createQueryBuilder('us')
                ->update()
                ->set('us.date_ended', ':dateEnded')
                ->set('us.is_active', '0')
                ->where('us.is_active = 1');
        $q->setParameters([
            ':dateEnded' => date('Y-m-d H:i:s'),
        ]);
        $query = $q->getQuery();
        $query->execute();
    }

    /**
     * Get users current subscription
     *
     * @param int $userInfoId
     *
     * @return array|null
     */
    public function getActiveSubscription($userInfoId)
    {
        $q = $this->createQueryBuilder('us')
                ->select('us, sp')
                ->innerJoin('us.subscription_plan', 'sp')
                ->where('us.user_info = :userInfoId AND us.is_active = 1');

        $params = [
            'userInfoId' => $userInfoId,
        ];
        $q->setParameters($params);
        $query = $q->getQuery();

        if (!$result = $query->getArrayResult()) {
            return null;
        }
        return current($result);
    }

    /**
     * @return UserSubscription[]
     */
    public function getExpiredCancelledSubscriptions()
    {
        $qb = $this->createQueryBuilder('us');

        $qb
            ->leftJoin('us.user_info', 'u')

            ->where('us.date_ended IS NOT NULL')
            ->andWhere('us.next_payment_date < :now')
            ->andWhere('us.is_active = :active')

            ->setParameter('now', new \DateTime())
            ->setParameter('active', true)
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * @param string[] $ids
     *
     * @return UserSubscription[]
     */
    public function getSubscriptionsBySubIds($ids)
    {
        $qb = $this->createQueryBuilder('us');

        $qb
            ->select('ui, us')
            ->join('us.user_info', 'ui')
            ->where($qb->expr()->in('us.paypal_subscr_id', $ids))
            ->orWhere($qb->expr()->in('us.stripe_subscr_id', $ids))
        ;

        return $qb->getQuery()->getResult();
    }

    public function findCountCancelledSubsByUser()
    {

        $yesterday = new \DateTime();
        $yesterday->sub(new \DateInterval('P1D'));
        $qb = $this->createQueryBuilder('us')
            ->select('count(us)')
            ->andWhere('us.cancel_date > :yesterday')
            ->andWhere('us.cancel_date < us.date_ended')
            ->setParameter('yesterday', $yesterday);

        return $qb->getQuery()->getSingleScalarResult();
    }

    public function findCountCancelledSubsByStripe()
    {

        $yesterday = new \DateTime();
        $yesterday->sub(new \DateInterval('P1D'));
        $qb = $this->createQueryBuilder('us')
            ->select('count(us)')
            ->where('us.is_active = false')
            ->andWhere('us.cancel_date > :yesterday')
            ->andWhere('us.cancel_date >= us.date_ended')
            ->setParameter('yesterday', $yesterday);

        return $qb->getQuery()->getSingleScalarResult();
    }
}

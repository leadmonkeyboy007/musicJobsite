<?php

namespace Vocalizr\AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * ProjectEscrowRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ProjectEscrowRepository extends EntityRepository
{
    /**
     * Get any project escrows that a user is involved with
     *
     * @param int $userInfoId
     *
     * @return array
     */
    public function getEscrowsUserInvolved($userInfoId)
    {
        return $this->createQueryBuilder('pe')
            ->select('pe, p, pb, ui')
            ->innerJoin('pe.user_info', 'ui')
            ->innerJoin('pe.project', 'p')
            ->leftJoin('pe.project_bid', 'pb')
            ->orderBy('pe.created_at', 'DESC')
            ->where(
                'pe.released_date IS NULL AND pe.refunded != :notRefunded AND
                (
                    p.employee_user_info = :userInfoId OR 
                    p.user_info = :userInfoId OR (pb.user_info = :userInfoId AND p.user_info = pb.user_info)
                )'
            )
            ->setParameter(':userInfoId', $userInfoId)
            ->setParameter(':notRefunded', 1)
            ->getQuery()
            ->execute()
        ;
    }
}
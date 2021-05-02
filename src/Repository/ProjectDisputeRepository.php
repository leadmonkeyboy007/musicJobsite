<?php

namespace App\Repository;

use DateTime;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;
use App\Entity\ProjectDispute;

/**
 * ProjectDisputeRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ProjectDisputeRepository extends EntityRepository
{
    /**
     * @param DateTime $beforeDate
     * @param int $remindersCount
     * @return ProjectDispute[]
     */
    public function getOpenDisputesOlderDateWithLesserReminders(DateTime $beforeDate, $remindersCount)
    {
        $qb = $this->getOpenDisputesOlderDateQb($beforeDate)
            ->andWhere('pd.remindersSentCount < :reminders_count')
            ->setParameter('reminders_count', $remindersCount)
        ;

        return $qb->getQuery()->getResult();
    }

    /**
     * @param DateTime $beforeDate
     * @return ProjectDispute[]
     */
    public function getOpenDisputesSinceDate(DateTime $beforeDate)
    {
        return $this->getOpenDisputesOlderDateQb($beforeDate)->getQuery()->getResult();
    }
    
    public function getDisputesByProject($projectId)
    {
        $q = $this->createQueryBuilder('pd')
                ->select('pd, ui, fui')
                ->innerJoin('pd.user_info', 'ui')
                ->innerJoin('pd.from_user_info', 'fui')
                ->where('pd.project = :projectId')
                ->orderBy('pd.created_at', 'DESC');

        $q->setParameter(':projectId', $projectId);
        return $q->getQuery()->execute();
    }

    /**
     * @param DateTime $beforeDate
     * @return QueryBuilder
     */
    private function getOpenDisputesOlderDateQb(DateTime $beforeDate)
    {
        $qb = $this->createQueryBuilder('pd');
        return $qb
            ->select('pd, ui, fui, p')
            ->innerJoin('pd.user_info', 'ui')
            ->innerJoin('pd.from_user_info', 'fui')
            ->innerJoin('pd.project', 'p')
            ->where('p.is_active = 1 AND p.completed_at IS NULL AND pd.accepted IS NULL')
            ->andWhere("pd.created_at <= :before_date")
            ->orderBy('pd.created_at', 'ASC')
            ->setParameter('before_date', $beforeDate)
        ;
    }
}

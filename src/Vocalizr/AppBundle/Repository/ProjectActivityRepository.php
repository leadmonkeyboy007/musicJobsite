<?php

namespace Vocalizr\AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Vocalizr\AppBundle\Entity\UserInfo;

/**
 * ProjectActivityRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ProjectActivityRepository extends EntityRepository
{
    /**
     * Get activity for user
     *
     * @param UserInfo $userInfo
     *
     * @return array
     */
    public function getActivityForUser($userInfo, $options = [])
    {
        $q = $this->createQueryBuilder('pa')
                ->select('pa, ui, p, aui, aua')
                ->innerJoin('pa.user_info', 'ui')
                ->leftJoin('pa.actioned_user_info', 'aui')
                ->leftJoin('aui.user_audio', 'aua', 'WITH', 'aua.default_audio = 1')
                ->innerJoin('pa.project', 'p')
                ->where('pa.user_info = :userInfo')
                ->orderBy('pa.created_at', 'DESC');

        $params = [
            ':userInfo' => $userInfo,
        ];
        $q->setParameters($params);

        if (isset($options['limit']) && $options['limit'] && $options['limit'] > 0) {
            $q->setMaxResults($options['limit']);
        }
        $query = $q->getQuery();

        return $query->getArrayResult();
    }

    /**
     * Find recent activity within timeframe by type, user and project
     *
     * @param string   $type
     * @param UserInfo $userInfo
     * @param Project  $project
     * @param int      $timeAgo  Time ago in seconds
     *
     * @return ProjectActivity|null
     */
    public function findRecentActivityByType($type, $userInfo, $project, $timeAgo = 300)
    {
        $q = $this->createQueryBuilder('pa');
        $q->select('pa, ui, aui, aua')
            ->leftJoin('pa.user_info', 'ui')
            ->leftJoin('pa.actioned_user_info', 'aui')
            ->leftJoin('aui.user_audio', 'aua', 'WITH', 'aua.default_audio = 1')
            ->where('pa.activity_type = :type AND pa.created_at >= :timeAgo')
            ->andWhere('pa.user_info = :userInfo')
            ->andWhere('pa.project = :project')
            ->orderBy('pa.created_at', 'DESC')
            ->setMaxResults(1);

        $dateTime = new \DateTime();
        $dateTime->modify('-' . $timeAgo . ' seconds');

        $params = [
            ':type'     => $type,
            ':timeAgo'  => $dateTime->format('Y-m-d H:i:s'),
            ':project'  => $project,
            ':userInfo' => $userInfo,
        ];

        $q->setParameters($params);
        $query = $q->getQuery();

        return $query->getOneOrNullResult();
    }
}
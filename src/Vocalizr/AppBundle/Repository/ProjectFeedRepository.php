<?php

namespace Vocalizr\AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * ProjectFeedRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ProjectFeedRepository extends EntityRepository
{
    /**
     * Get feed by project id
     *
     * @param int $projectId
     *
     * @return array
     */
    public function getFeed($projectId, $lastFeedId = null)
    {
        $q = $this->createQueryBuilder('pf')
                ->select('pf, ui')
                ->innerJoin('pf.from_user_info', 'ui')
                ->where('pf.project = :projectId')
                ->orderBy('pf.created_at', 'DESC');

        $params = [
            ':projectId' => $projectId,
        ];

        if ($lastFeedId) {
            $params[':lastFeedId'] = $lastFeedId;
            $q->andWhere('pf.id > :lastFeedId');
        }

        $q->setParameters($params);
        $query = $q->getQuery();

        return $query->getArrayResult();
    }

    /**
     * Get feed by user
     *
     * @param UserInfo $userInfo
     *
     * @return array
     */
    public function getFeedForUser($userInfo, $options = [])
    {
        $q = $this->createQueryBuilder('pf')
                ->select('pf, ui')
                ->innerJoin('pf.from_user_info', 'ui')
                                ->where('pf.user_info = :userInfo')
                ->orderBy('pf.created_at', 'DESC');

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
     * Update feed items as read for project / user
     *
     * @param int $projectId
     * @param int $userInfoId
     *
     * @return int Num of rows updated
     */
    public function updateFeedItemsAsRead($projectId, $userInfoId)
    {
        $q = $this->createQueryBuilder('pf');
        $q = $q->update()
                ->set('pf.feed_read', $q->expr()->literal(true))
                ->where('pf.project = :projectId AND pf.from_user_info = :userInfoId')
                ->andWhere('pf.feed_read = 0');
        $params = [
            ':projectId'  => $projectId,
            ':userInfoId' => $userInfoId,
        ];
        $q->setParameters($params);
        return $q->getQuery()->execute();
    }
}
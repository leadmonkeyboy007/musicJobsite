<?php

namespace Vocalizr\AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Vocalizr\AppBundle\Entity\Project;
use Vocalizr\AppBundle\Entity\ProjectAudio;
use Vocalizr\AppBundle\Entity\UserInfo;

/**
 * ProjectRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ProjectRepository extends EntityRepository
{
    /**
     * Get project by uuid
     *
     * @param string $uuid
     *
     * @return Project
     */
    public function getProjectByUuid($uuid)
    {
        $q = $this->createQueryBuilder('p')
            ->select('p, u, pb, pbu, ut, pe, pc')
            ->innerJoin('p.user_info', 'u')
            ->leftJoin('p.project_bid', 'pb')
            ->leftJoin('pb.user_info', 'pbu')
            ->leftJoin('p.project_escrow', 'pe')
            ->leftJoin('p.project_contracts', 'pc')
            ->leftJoin('p.user_transaction', 'ut', 'WITH', 'ut.success = 1')

            ->where('p.uuid = :uuid')
            ->andWhere('u.is_active = 1')
        ;

        $params = [
            ':uuid' => $uuid,
        ];
        $q->setParameters($params);

        $query = $q->getQuery();

        try {
            return $query->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return null;
        }
    }

    /**
     * Get projects in progress which user is involved with
     * Either they are the owner, or they are the bidder
     *
     * @param int $userInfoId
     *
     * @return array
     */
    public function getProjectsInProgressByUserInvolved($userInfoId)
    {
        $q = $this->createQueryBuilder('p')
                ->select('pb,p')
                ->innerJoin('p.project_bid', 'pb')
                ->innerJoin('pb.user_info', 'ui')
                ->where('(p.user_info = :userInfoId OR pb.user_info = :userInfoId) AND p.is_complete = 0');
        $q->setParameter(':userInfoId', $userInfoId);

        $query = $q->getQuery();

        return $query->execute();
    }

    /**
     * Get the number of projects this user is involved in
     *
     * @param UserInfo $userInfo
     *
     * @return int
     */
    public function getProjectCountForUser($userInfo, $filter = null)
    {
        $q = $this->createQueryBuilder('p');
        $q->select('count(p)')
          ->leftJoin('p.project_bid', 'pb')
          ->where(
              $q->expr()->orX(
                    $q->expr()->eq('p.user_info', ':user'),
                    $q->expr()->eq('pb.user_info', ':user')
                )
          )
          ->setParameter('user', $userInfo);

        switch ($filter) {
            case 'active':
                $q->andWhere('p.project_bid is not null')
                  ->andWhere('p.is_complete = :complete')
                  ->setParameter('complete', false);
                break;
            case 'complete':
                $q->andWhere('p.is_complete = :complete')
                  ->setParameter('complete', true);
                break;
            case 'expired':
                $q->andWhere('p.project_bid is null')
                  ->andWhere('p.bids_due < :now')
                  ->setParameter('now', new \DateTime());
                break;
            case 'open':
                $q->andWhere('p.project_bid is null')
                  ->andWhere('p.bids_due >= :now')
                  ->setParameter('now', new \DateTime());
                break;
        }
        $query = $q->getQuery();

        return $query->getSingleScalarResult();
    }

    /**
     * Get projects that user can be invited to
     *
     * @param int $userInfoId       User id of projects you want to list
     * @param int $inviteUserInfoId User id of member you want to invite
     *
     * @return array
     */
    public function getProjectsToInvite($userInfoId, $inviteUserInfoId)
    {
        $q = $this->createQueryBuilder('p')
                ->select('p, pi, pb')
                ->leftJoin('p.project_bids', 'pb', 'WITH', 'pb.user_info = :inviteUserInfoId')
                ->leftJoin('p.project_invites', 'pi', 'WITH', 'pi.user_info = :inviteUserInfoId')
                ->where('p.user_info = :userInfoId AND p.project_bid IS NULL AND p.hire_user IS NULL AND p.published_at IS NOT NULL')
                ->andWhere('p.bids_due >= :now');

        $params = [
            ':userInfoId'       => $userInfoId,
            ':inviteUserInfoId' => $inviteUserInfoId,
            ':now'              => date('Y-m-d H:i:s'),
        ];
        $q->setParameters($params);
        $query = $q->getQuery();
        return $query->getArrayResult();
    }

    public function findProject($searchTerm)
    {
        $q = $this->createQueryBuilder('p');
        $q->select('p')
          ->innerJoin('p.user_info', 'u')
          ->where(
              $q->expr()->orX(
                    $q->expr()->eq('p.uuid', ':uuid'),
                    $q->expr()->like('u.email', ':searchTerm'),
                    $q->expr()->like('u.username', ':searchTerm'),
                    $q->expr()->like('u.display_name', ':searchTerm'),
                    $q->expr()->like('p.title', ':searchTerm')
                )
          )
          ->setParameter('uuid', $searchTerm)
          ->setParameter('searchTerm', '%' . $searchTerm . '%');

        $query = $q->getQuery();

        return $query->execute();
    }

    public function getOpenProjectsByUser($userInfo, $loggedInUser)
    {
        $q = $this->createQueryBuilder('p')
                ->select('p, pa')
                ->leftJoin('p.project_audio', 'pa', 'WITH', 'pa.flag = :audioFlag')
                ->andWhere('p.user_info = :userInfo and p.bids_due >= :now and p.employee_user_info IS NULL and p.publish_type = :type AND p.is_active = 1');

        $params = [
            'userInfo'  => $userInfo,
            'now'       => date('Y-m-d H:i:s'),
            'audioFlag' => ProjectAudio::FLAG_FEATURED,
            'type'      => 'public',
        ];

        if ($loggedInUser) {
            $q->addSelect('pbs');
            $q->leftJoin('p.project_bids', 'pbs', 'WITH', 'pbs.user_info = :userInfoId');
            $params[':userInfoId'] = $loggedInUser->getId();
        }
        $q->setParameters($params);
        $query = $q->getQuery();

        return $query->execute();
    }

    public function getUserOpenProjectCount($userInfo)
    {
        $q = $this->createQueryBuilder('p')
                ->select('COUNT(p)')
                ->andWhere('p.user_info = :userInfo and p.bids_due >= :now and p.employee_user_info IS NULL and p.publish_type = :type');

        $params = [
            'userInfo' => $userInfo,
            'now'      => date('Y-m-d H:i:s'),
            'type'     => 'public',
        ];
        $q->setParameters($params);
        $query = $q->getQuery();
        return $query->getSingleScalarResult();
    }

    /**
     * @param UserInfo $userInfo
     * @return Project|null
     * @throws NonUniqueResultException
     */
    public function findLastCompletedProjectWhereUserEmployee(UserInfo $userInfo)
    {
        $qb = $this->createQueryBuilder('p');
        $qb
            ->andWhere(
                    $qb->expr()->eq('p.employee_user_info', ':userInfo')
            )
            ->andWhere($qb->expr()->isNotNull('p.completed_at'))

            ->orderBy('p.completed_at', 'DESC')
            ->setParameter('userInfo', $userInfo)

            ->setMaxResults(1)
        ;

        return $qb->getQuery()->getOneOrNullResult();
    }

}

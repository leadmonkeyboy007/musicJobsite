<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;
use App\Entity\MessageThread;
use App\Entity\Project;

/**
 * MessageThreadRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class MessageThreadRepository extends EntityRepository
{
    /**
     * Get all the message threads for a particular user
     *
     * @param int $userInfoId
     *
     * @return array
     */
    public function findThreadsForUser($user, $project = null, $firstResult = 0, $maxResults = 20)
    {
        $q = $this->createQueryBuilder('mt');
        $q->select('mt, p, e, b')
            ->innerJoin('mt.employer', 'e')
            ->innerJoin('mt.bidder', 'b')
            ->leftJoin('mt.project', 'p')
            //->leftJoin('mt.messages', 'm')
            //->leftJoin('m.message_files', 'mf')
            ->where($q->expr()->orx(
                $q->expr()->andx(
                    $q->expr()->eq('mt.employer', ':user')
                ),
                $q->expr()->andx(
                    $q->expr()->eq('mt.bidder', ':user')
                )
            ))
            ->andWhere('e.is_active = 1')
            ->andWhere('b.is_active = 1')
            ->orderBy('mt.last_message_at', 'DESC');
        if ($project) {
            $q->andWhere('mt.project = :project')
              ->setParameter('project', $project);
        }
        $q->setParameter(':user', $user);

        $q->setFirstResult($firstResult);
        $q->setMaxResults($maxResults);

        $query = $q->getQuery();

        return $query->execute();
    }

    public function findThreadsForUserCount($user, $project = null)
    {
        $q = $this->createQueryBuilder('mt');
        $q->select('count(mt.id)')
            ->innerJoin('mt.employer', 'e')
            ->innerJoin('mt.bidder', 'b')
            ->leftJoin('mt.project', 'p')
            //->leftJoin('mt.messages', 'm')
            //->leftJoin('m.message_files', 'mf')
            ->where($q->expr()->orx(
                $q->expr()->andx(
                    $q->expr()->eq('mt.employer', ':user')
                ),
                $q->expr()->andx(
                    $q->expr()->eq('mt.bidder', ':user')
                )
            ))
            ->andWhere('e.is_active = 1')
            ->andWhere('b.is_active = 1')
            ->orderBy('mt.last_message_at', 'DESC');
        if ($project) {
            $q->andWhere('mt.project = :project')
              ->setParameter('project', $project);
        }
        $q->setParameter(':user', $user);

        $query = $q->getQuery();

        return $query->getSingleScalarResult();
    }

    public function closeOpenThreadsBetweenUsers($user, $otherUser)
    {
        $q = $this->createQueryBuilder('mt');
        $q->update('App:MessageThread', 'mt');
        $q->set('mt.is_open', 0);
        $q->where('(mt.employer = :user AND mt.bidder = :otherUser) OR (mt.employer = :otherUser AND mt.bidder = :user)');
        $q->andWhere('mt.is_open = 1');

        $q->setParameters([
            'user'      => $user,
            'otherUser' => $otherUser,
        ]);

        $query = $q->getQuery();

        return $query->execute();
    }

    /**
     * @param $user
     *
     * @return mixed
     *
     * @throws \Doctrine\ORM\Query\QueryException
     */
    public function getThreadCountUserMonth($user)
    {
        $q = $this->createQueryBuilder('mt');
        $q->select('count(mt.id)');
        $q->where("mt.employer = :user AND DATE_FORMAT(mt.created_at, '%m-%Y') = :date");

        $q->setParameters([
            'user' => $user,
            'date' => date('m-Y'),
        ]);

        $query = $q->getQuery();

        return $query->getSingleScalarResult();
    }

    /** @noinspection PhpDocMissingThrowsInspection */

    /**
     * @param Project $gig
     *
     * @return mixed
     */
    public function deleteThreadsForGig($gig)
    {
        $qb = $this->createQueryBuilder('mt');

        $qb
            ->select()
            ->leftJoin('mt.bidder', 'bu')
            ->leftJoin('mt.employer', 'eu')
            ->where('mt.project = :gig')
            ->andWhere('mt.num_employer_unread > 0 OR mt.num_bidder_unread > 0')
            ->setParameter('gig', $gig)
        ;

        /** @var MessageThread[] $unreadThreads */
        $unreadThreads = $qb->getQuery()->execute();

        foreach ($unreadThreads as $thread) {
            $thread->getBidder()->setNumUnreadMessages($thread->getBidder()->getNumUnreadMessages() - $thread->getNumBidderUnread());
            $thread->getEmployer()->setNumUnreadMessages($thread->getEmployer()->getNumUnreadMessages() - $thread->getNumEmployerUnread());
            $this->_em->persist($thread->getBidder());
            $this->_em->persist($thread->getEmployer());
        }

        /** @noinspection PhpUnhandledExceptionInspection */
        $this->_em->flush();

        $qb = $this->createQueryBuilder('mt');

        $qb
            ->delete()
            ->where('mt.project = :gig')
            ->setParameter('gig', $gig)
        ;

        return $qb->getQuery()->execute();
    }
}

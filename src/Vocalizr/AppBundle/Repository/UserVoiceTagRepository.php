<?php

namespace Vocalizr\AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Vocalizr\AppBundle\Entity\UserVoiceTag;

/**
 * UserVoiceTagRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class UserVoiceTagRepository extends EntityRepository
{
    /**
     * @param UserVoiceTag[]|int[] $tags
     */
    public function getLikedTags($tags)
    {
        $tagIds = [];
        foreach ($tags as $tag) {
            if (is_numeric($tag)) {
                $tagIds = $tag;
            } else {
                $tagIds = $tag->getId();
            }
        }

        $qb = $this->createQueryBuilder('uvt');
        $qb
            ->select('uwt.id')
            ->join('uwt.user_voice_tag_votes', 'uvtv')
        ;
    }

    /**
     * Get voice tag ids/names for user
     *
     * @param int $userInfoId
     *
     * @return array
     */
    public function getVoiceTagIdsForUser($userInfoId)
    {
        $q = $this->createQueryBuilder('uvt')
                ->select('uvt.id, UPPER(vt.name) as tag')
                ->innerJoin('uvt.voice_tag', 'vt')
                ->where('uvt.user_info = :userInfoId');
        $params = [
            ':userInfoId' => $userInfoId,
        ];
        $q->setParameters($params);

        if ($results = $q->getQuery()->getArrayResult()) {
            $newResults = [];
            foreach ($results as $result) {
                $newResults[$result['id']] = $result['tag'];
            }
            return $newResults;
        }
        return [];
    }

    /**
     * Delete records by user info id
     *
     * @param int $userInfoId
     *
     * @return int
     */
    public function deleteByUserInfoId($userInfoId)
    {
        $q = $this->createQueryBuilder('uv')
                ->delete()
                ->where('uv.user_info = :userInfoId');

        $q->setParameter(':userInfoId', $userInfoId);

        return $q->getQuery()->execute();
    }

    /**
     * Get user voice tags by user info id
     *
     * @param int $userInfoId
     *
     * @return array
     */
    public function getUserVoiceTags($userInfoId)
    {
        $q = $this->createQueryBuilder('uv')
                ->select('uv, vt')
                ->innerJoin('uv.voice_tag', 'vt')
                ->where('uv.user_info = :userInfoId')
                ->orderBy('uv.id', 'ASC');
        $q->setParameter(':userInfoId', $userInfoId);

        $query = $q->getQuery();

        $results       = $query->getArrayResult();
        $userVoiceTags = [];

        foreach ($results as $result) {
            $userVoiceTags[$result['voice_tag']['id']] = ucwords($result['voice_tag']['name']);
        }

        return $userVoiceTags;
    }

    /**
     * Get user voice tags and join voted from
     *
     * @param int $userInfoId
     * @param int $fromUserInfoId
     */
    public function getByUserJoinVotedUser($userInfoId, $fromUserInfoId = null)
    {
        $q = $this->createQueryBuilder('uvt')
            ->select('uvt, vt')
            ->innerJoin('uvt.voice_tag', 'vt')
            ->where('uvt.user_info = :userInfoId')
        ;

        $params = [
            ':userInfoId' => $userInfoId,
        ];

        if ($fromUserInfoId) {
            $q->addSelect('uvtv');
            $q->leftJoin(
                'uvt.user_voice_tag_votes',
                'uvtv',
                'WITH',
                'uvtv.from_user_info = :fromUserInfoId'
            );
            $params[':fromUserInfoId'] = $fromUserInfoId;
        }

        $q->setParameters($params);
        $query = $q->getQuery();

        return $query->execute();
    }

    /**
     * Get single user voice tag by id and join voted
     *
     * @param int $id
     * @param int $fromUserInfoId
     *
     * @return UserVoiceTag|null
     */
    public function getByIdJoinVotedUser($id, $fromUserInfoId = null)
    {
        $q = $this->createQueryBuilder('uvt')
            ->select('uvt, vt')
            ->innerJoin('uvt.voice_tag', 'vt')
            ->where('uvt.id = :id')
        ;

        $params = [
            ':id' => $id,
        ];

        if ($fromUserInfoId) {
            $q->addSelect('uvtv');
            $q->leftJoin(
                'uvt.user_voice_tag_votes',
                'uvtv',
                'WITH',
                'uvtv.from_user_info = :fromUserInfoId'
            );
            $params[':fromUserInfoId'] = $fromUserInfoId;
        }

        $q->setParameters($params);
        $query = $q->getQuery();

        try {
            return $query->getOneOrNullResult();
        } catch (Doctrine\ORM\NonUniqueResultException $e) {
            return null;
        }
    }

    /**
     * Get top 10 tags that have votes
     *
     * @return array
     */
    public function getTop10()
    {
        $q = $this->createQueryBuilder('uvt')
                ->select('uvt, vt')
                ->addSelect('count(uvt) as total_users')
                ->innerJoin('uvt.voice_tag', 'vt')
                ->where('uvt.agree >= 1')
                ->groupBy('uvt.voice_tag')
                ->orderBy('total_users', 'DESC')
                ->setMaxResults(10);

        $query = $q->getQuery();
        $query->useResultCache(true, 3600, 'top10voicetags');

        return $query->execute();
    }
}
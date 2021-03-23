<?php

namespace Vocalizr\AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

/**
 * GenreRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class GenreRepository extends EntityRepository
{
    public function getAll()
    {
        $qb = $this->createQueryBuilder('g')
                ->orderBy('g.title', 'ASC');
        $query = $qb->getQuery();
        $query->useResultCache(true, 86400, 'genres');
        $query->useQueryCache(true);
        $results = $query->execute();

        return $results;
    }
}

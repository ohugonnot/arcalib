<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

class TagRepository extends EntityRepository
{
    /**
     * @param $tag
     * @return mixed
     */
    public function searchTag($tag)
    {
        $qb = $this->createQueryBuilder('t')//add select and array for JSON
        ->where('t.nom LIKE :tag')
            ->setParameter("tag", $tag . '%')
            ->setMaxResults('5');

        return $qb->getQuery()->getResult();
    }

    /**
     * @param $search
     * @return Query
     */
    public function getQuery($search)
    {
        $queryBuilder = $this->createQueryBuilder('t')
            ->select('COUNT(DISTINCT e) AS HIDDEN nbEssais', 't', 'COUNT(i) AS HIDDEN nbInclusions')
            ->leftJoin('t.essais', 'e')
            ->leftJoin('e.inclusions', 'i')
            ->addSelect("i","e")
            ->where("t.nom like :search or t.classe like :search")
            ->groupBy('t.id')
            ->setParameter('search', '%' . $search . '%');

        return $queryBuilder->getQuery();
    }
}

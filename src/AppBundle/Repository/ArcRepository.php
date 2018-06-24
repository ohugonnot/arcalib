<?php

namespace AppBundle\Repository;

/**
 * ArcRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ArcRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @param $search
     * @return \Doctrine\ORM\Query
     */
    public function getQuery($search)
    {
        $queryBuilder = $this->createQueryBuilder("a")
            ->where("a.nomArc like :search or  a.dect like :search or  a.tel like :search or  a.mail like :search or  a.droit like :search")
            ->setParameters(array(
                'search' => '%' . $search . '%',
             ));

        return $queryBuilder->getQuery();
    }
}

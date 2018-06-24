<?php

namespace AppBundle\Repository;

/**
 * ActualiteRepository
 *
 * This class was generated by the Doctrine ORM. Add your own custom
 * repository methods below.
 */
class ActualiteRepository extends \Doctrine\ORM\EntityRepository
{
    /**
     * @param $search
     * @return \Doctrine\ORM\Query
     */
    public function getQuery($search)
    {
        $queryBuilder = $this->createQueryBuilder("a")
            ->where("a.titre like :search or a.text like :search or a.date like :search ")
            ->setParameters(array(
                'search' => '%' . $search . '%',
            ));

        return $queryBuilder->getQuery();
    }
}

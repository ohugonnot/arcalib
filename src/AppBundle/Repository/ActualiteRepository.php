<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

class ActualiteRepository extends EntityRepository
{
    /**
     * @param $search
     * @return Query
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

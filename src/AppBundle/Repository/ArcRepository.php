<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;

class ArcRepository extends EntityRepository
{
    /**
     * @param $search
     * @return Query
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

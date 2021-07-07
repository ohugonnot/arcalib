<?php

namespace AppBundle\Repository;

use Doctrine\ORM\EntityRepository;

class AnnuaireRepository extends EntityRepository
{
    /**
     * @param $query
     * @return array
     */
    public function findAdvancedArray($query)
    {
        $queryBuilder = $this->createQueryBuilder('a')
            ->leftJoin('a.essai', 'e')
            ->addSelect('e')
            ->groupBy("a.id")
            ->setMaxResults(25);

        foreach ($query as $k => $q)
            if ($q != '')
                $queryBuilder->andWhere("a.mail like :q$k or a.nom like :q$k  or a.fonction like :q$k  or a.telephone like :q$k or a.prenom like :q$k or e.nom like :q$k or a.societe like :q$k")
                    ->setParameter("q$k", '%' . $q . "%");

        return $queryBuilder->getQuery()->getArrayResult();
    }

    public function getQuery($search)
    {
        $queryBuilder = $this->createQueryBuilder("a")
            ->leftJoin("a.essai", "e")
            ->addSelect("e")
            ->where("a.nom like :search or a.fonction like :search or a.societe like :search or a.mail like :search or a.telephone like :search or a.portable like :search or a.fax like :search or a.autre like :search or e.nom like :search")
            ->setParameters(array(
              'search' => '%' . $search . '%',
            ));

        return $queryBuilder->getQuery();
    }
}

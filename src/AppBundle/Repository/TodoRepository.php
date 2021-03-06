<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Todo;
use AppBundle\Entity\User;
use DateTime;
use Doctrine\ORM\EntityRepository;
use Exception;

class TodoRepository extends EntityRepository
{
    /**
     * @param User $user
     * @return array
     * @throws Exception
     */
    public function findAlertes(User $user)
    {
        $queryBuilder = $this->createQueryBuilder('t')
            ->leftJoin('t.destinataires', 'd')
            ->where("t.dateAlerte <= :now and (t.niveauResolution != '" . TODO::RESOLU . "' and t.niveauResolution != '" . TODO::RESOLU_AVEC_REMARQUES . "') and d = :user and t.alerte = 1")
            ->setParameter("now", new DateTime())
            ->setParameter("user", $user);

        return $queryBuilder->getQuery()->getArrayResult();
    }

    /**
     * @param User $user
     * @param $lastVisite
     * @return array
     */
    public function findNewTodos(User $user, $lastVisite)
    {
        $queryBuilder = $this->createQueryBuilder('t')
            ->leftJoin('t.destinataires', 'd')
            ->where("d = :user and (t.niveauResolution != '" . TODO::RESOLU . "' and t.niveauResolution != '" . TODO::RESOLU_AVEC_REMARQUES . "')")
            ->setParameter("user", $user);

        if (false && $lastVisite && $lastVisite instanceof Datetime)
            $queryBuilder->andWhere("t.createdAt > :lastVisite")->setParameter("lastVisite", $lastVisite);

        return $queryBuilder->getQuery()->getArrayResult();
    }
}

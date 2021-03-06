<?php

namespace AppBundle\Repository;

use AppBundle\Entity\EI;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class EiRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param User $user
     * @return QueryBuilder
     */
    private function joinUserWhereUser(QueryBuilder $queryBuilder, User $user)
    {
        $queryBuilder->leftJoin('ei.inclusion', 'i')
            ->leftJoin('i.essai', 'e')
            ->leftJoin('i.patient', 'p');

        if (!$user->getEssais()->isEmpty() || $user->getRulesProtocole() == User::NO_PROTOCOLE)
            $queryBuilder->leftJoin("e.users", "u")
                ->andWhere("u = :user")
                ->setParameter("user", $user);

        return $queryBuilder;
    }

    public function getQuery(User $user, $search, $id)
    {
        $queryBuilder = $this->createQueryBuilder('ei')
            ->leftJoin("ei.term","t")
            ->addSelect("t")
            ->where("ei.details like :search or ei.suivi like :search")
            ->andWhere('i.id = :id')
            ->groupBy('ei.id')
            ->setParameter('search', '%' . $search . '%')
            ->setParameter('id', $id);

        $queryBuilder = $this->joinUserWhereUser($queryBuilder, $user);

        return $queryBuilder->getQuery();

    }

    public function findAlerteEi(User $user)
    {
        $queryBuilder = $this->createQueryBuilder('ei')
            ->leftJoin("ei.users","au")
            ->andWhere("au = :userAlerte and ei.suivi = '". EI::ALERTE ."'")
            ->setParameter("userAlerte", $user)
            ->groupBy('ei.id');

        $queryBuilder = $this->joinUserWhereUser($queryBuilder, $user);

        return $queryBuilder->getQuery()->getResult();

    }
}

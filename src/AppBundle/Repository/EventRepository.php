<?php

namespace AppBundle\Repository;

use AppBundle\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class EventRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param User $user
     * @return QueryBuilder
     */
    private function joinUserWhereUser(QueryBuilder $queryBuilder, User $user)
    {
        $queryBuilder->leftJoin('ev.inclusion', 'i')
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
        $queryBuilder = $this->createQueryBuilder('ev')
            ->where("ev.date like :search or ev.detail like :search or ev.titre like :search")
            ->andWhere('i.id = :id')
            ->groupBy('ev.id')
            ->setParameter('search', '%' . $search . '%')
            ->setParameter('id', $id);

        $queryBuilder = $this->joinUserWhereUser($queryBuilder, $user);

        return $queryBuilder->getQuery();

    }
}

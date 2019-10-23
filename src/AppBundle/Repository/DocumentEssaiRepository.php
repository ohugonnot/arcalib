<?php

namespace AppBundle\Repository;

use AppBundle\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;

class DocumentEssaiRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param User $user
     * @return QueryBuilder
     */
    private function joinUserWhereUser(QueryBuilder $queryBuilder, User $user)
    {
        $queryBuilder->leftJoin('d.essai', 'e');

        if (!$user->getEssais()->isEmpty() || $user->getRulesProtocole() == User::NO_PROTOCOLE)
            $queryBuilder->leftJoin("e.users", "u")
                ->andWhere("u = :user")
                ->setParameter("user", $user);

        return $queryBuilder;
    }

    public function getQuery(User $user, $search, $id)
    {
        $queryBuilder = $this->createQueryBuilder('d')
            ->select('d')
            ->where("d.date like :search or d.titre like :search or d.type like :search or d.details like :search or d.file like :search")
            ->andWhere('e.id = :id')
            ->groupBy('d.id')
            ->setParameter('search', '%' . $search . '%')
            ->setParameter('id', $id);

        $queryBuilder = $this->joinUserWhereUser($queryBuilder, $user);

        return $queryBuilder->getQuery();
    }

    public function findPDF(User $user, $pdf)
    {
        $queryBuilder = $this->createQueryBuilder('d')
            ->select('d')
            ->andWhere('d.file = :pdf')
            ->setParameter('pdf',$pdf);

        $queryBuilder = $this->joinUserWhereUser($queryBuilder, $user);

        try {
            return $queryBuilder->getQuery()->getOneOrNullResult();
        } catch (NonUniqueResultException $e) {
            return $queryBuilder->getQuery()->getFirstResult();
        }
    }
}

<?php

namespace AppBundle\Repository;

use AppBundle\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class factureRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param User $user
     * @return QueryBuilder
     */
    private function joinUserWhereUser(QueryBuilder $queryBuilder, User $user)
    {
        if (!$user->getEssais()->isEmpty() || $user->getRulesProtocole() == User::NO_PROTOCOLE)
            $queryBuilder->leftJoin('e.users', 'u')
                ->andWhere("u = :user")
                ->setParameter("user", $user);

        return $queryBuilder;
    }

    /**
     * @param User $user
     * @param $search
     * @return mixed
     */
    public function getQuery(User $user, $search)
    {
        $queryBuilder = $this->createQueryBuilder('f')
            ->addSelect('e')
            ->leftJoin('f.essai', 'e')
            ->groupBy('f.id');

        if (!empty($search)) {
            $queryBuilder->where("f.numero like :search or f.numInterne like :search or f.projet like :search or f.payeur like :search or f.receveur like :search or f.type like :search or f.statut like :search or f.note like :search or f.responsable like :search or e.nom like :search or f.creditDebit like :search or f.type like :search")
                ->setParameter('search', '%' . $search . '%');
        }

        $queryBuilder = $this->joinUserWhereUser($queryBuilder, $user);

        return $queryBuilder->getQuery();
    }
}

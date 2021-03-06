<?php

namespace AppBundle\Repository;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class TraitementRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param User $user
     * @return QueryBuilder
     */
    private function joinUserWhereUser(QueryBuilder $queryBuilder, User $user)
    {
        $queryBuilder->leftJoin('t.inclusion', 'i')
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
        $queryBuilder = $this->createQueryBuilder('t')
            ->select('t')
            ->where("t.attributionAt like :search 
            or t.priseAt like :search 
            or t.traitement like :search 
            or t.nombre like :search 
            or t.notes like :search 
            or t.verificateur like :search 
            or t.peremptionAt like :search 
            or t.numLot like :search
            or t.retourAt like :search
            or t.traitementRetour like :search
            or t.nombreRetour like :search
            or t.notesRetour like :search
            or t.numLotRetour like :search
            or t.verificateurRetour like :search")
            ->andWhere('i.id = :id')
            ->groupBy('t.id')
            ->setParameter('search', '%' . $search . '%')
            ->setParameter('id', $id);

        $queryBuilder = $this->joinUserWhereUser($queryBuilder, $user);

        return $queryBuilder->getQuery();
    }
}

<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Inclusion;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\QueryBuilder;

class MedecinRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param User $user
     * @return QueryBuilder
     */
    private function joinUserWhereUser(QueryBuilder $queryBuilder, User $user)
    {
        if (!$user->getEssais()->isEmpty() || $user->getRulesProtocole() == User::NO_PROTOCOLE)
            $queryBuilder
                ->leftJoin('e.users', 'u')
                ->andWhere("u = :user")
                ->setParameter("user", $user);

        return $queryBuilder;
    }

    public function getQuery(User $user, $search)
    {
        $queryBuilder = $this->createQueryBuilder('m')
            ->select('m')
            ->leftJoin('m.inclusions', 'i')
            ->leftJoin('i.essai', 'e')
            ->leftJoin('i.patient', 'p')
            ->leftJoin('m.service', 's')
            ->addSelect("s")
            ->where("m.nom like :search or m.prenom like :search or s.nom like :search or m.email like :search or m.portable like :search  or m.dect like :search or m.portable like :search or m.secTel like :search")
            ->groupBy('m.id')
            ->setParameter('search', '%' . $search . '%');

        $queryBuilder = $this->joinUserWhereUser($queryBuilder, $user);

        return $queryBuilder->getQuery();
    }

    /**
     * @param $query
     * @param User $user
     * @return array
     */
    public function findAdvancedArray($query, User $user)
    {
        $query = explode(" ", $query);
        $queryBuilder = $this->createQueryBuilder('m')
            ->leftJoin('m.inclusions', 'i')
            ->leftJoin('i.patient', 'p')
            ->leftJoin('i.essai', 'e')
            ->orderBy('m.nom', 'ASC');

        $queryBuilder = $this->joinUserWhereUser($queryBuilder, $user);

        $queryBuilderTest = clone $queryBuilder;
        $queryBuilderTest->groupBy("m.id")->setMaxResults(25);

        foreach ($query as $k => $q)
            if ($q != '')
                $queryBuilderTest
                    ->andWhere("m.nom like :q$k or m.prenom like :q$k")
                    ->setParameter("q$k", '%' . $q . "%");

        $results = $queryBuilderTest->getQuery()->getArrayResult();

        $ids = [];
        foreach ($results as $medecin)
            $ids[] = $medecin["id"];

        $queryBuilder
            ->andwhere("m.id IN(:ids)")
            ->setParameter('ids', $ids);

        $queryBuilderSecondLevel = clone $queryBuilder;

        $queryBuilder
            ->addSelect('i')
            ->addSelect('p')
            ->addSelect('e')
            ->andWhere("i.statut = '" . Inclusion::SCREEN . "' or i.statut = '" . Inclusion::OUI_EN_COURS . "'");

        $firstLevel = $this->idMedecinOnKeyArray($queryBuilder->getQuery()->getArrayResult());
        $secondLevel = $this->idMedecinOnKeyArray($queryBuilderSecondLevel->getQuery()->getArrayResult());

        return array_values($firstLevel + $secondLevel);
    }

    /**
     * @param $arrayMedecins
     * @return array
     */
    private function idMedecinOnKeyArray($arrayMedecins)
    {
        $arrayById = [];
        foreach ($arrayMedecins as $medecin)
            $arrayById[$medecin["id"]] = $medecin;

        return $arrayById;
    }

    /**
     * @param $nomPrenomConact
     * @return mixed
     * @throws NonUniqueResultException
     */
    public function findByNomPrenomConcat($nomPrenomConact)
    {
        $qb = $this->createQueryBuilder('m');
        $qb->where($qb->expr()->concat('m.nom', $qb->expr()->concat($qb->expr()->literal(' '), 'm.prenom')) . " = :nomPrenomConact")
            ->setParameter('nomPrenomConact', $nomPrenomConact);

        return $qb->getQuery()->getOneOrNullResult();
    }
}

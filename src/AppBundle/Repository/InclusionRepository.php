<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Inclusion;
use AppBundle\Entity\User;
use DateTime;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;

class InclusionRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param User $user
     * @return QueryBuilder
     */
    private function joinUserWhereUser(QueryBuilder $queryBuilder, User $user)
    {
        $queryBuilder->leftJoin('i.essai', 'e');

        if (!$user->getEssais()->isEmpty() || $user->getRulesProtocole() == User::NO_PROTOCOLE)
            $queryBuilder
                ->leftJoin('e.users', 'u')
                ->andWhere("u = :user")
                ->setParameter("user", $user);

        return $queryBuilder;
    }

    /**
     * @param User $user
     * @param $searchId
     * @param $search
     * @param array $filters
     * @return Query
     */
    public function getQuery(User $user, $searchId, $search, $filters = [])
    {
        $queryBuilder = $this->createQueryBuilder('i')
            ->select('i')
            ->leftJoin('i.patient', 'p')
            ->leftJoin('i.documents','d')
            ->leftJoin('i.traitements','t')
            ->leftJoin('i.eis','ei')
            ->leftJoin('i.events','ev')
            ->addSelect("p", "e", "d","t", "ei", "ev")
            ->where("p.nom like :search or p.prenom like :search or i.statut like :search or i.datInc like :search or e.nom like :search or i.numInc like :search or i.idInterne = :searchId or i.id = :searchId")
            ->setParameter('searchId', $searchId)
            ->setParameter('search', '%' . $search . '%');

         if ($filters)
            foreach($filters as $param => $value) {
                 if ($value == null)
                    continue;
                $paramValue = $param.'_value';
                $queryBuilder->andWhere("i.$param = :$paramValue")
                    ->setParameter($paramValue, $value);
            }

        $queryBuilder = $this->joinUserWhereUser($queryBuilder, $user);

        return $queryBuilder->getQuery();
    }

    /**
     * @param DateTime|null $debut
     * @param DateTime|null $fin
     * @return array
     */
    public function findByDate(?DateTime $debut, ?DateTime $fin = null)
    {
        if ($fin == null)
            $fin = date("Y-m-d H:i:s");

        $queryBuilder = $this->createQueryBuilder('i');
        $queryBuilder
            ->where('i.datInc >= :debut')
            ->andwhere('i.datInc <= :fin')
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param User $user
     * @return array
     */
    public function findAllByUser(User $user)
    {
        $queryBuilder = $this->createQueryBuilder('i');
        $queryBuilder = $this->joinUserWhereUser($queryBuilder, $user);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param User $user
     * @return array
     */
    public function findByStatutScreen(User $user)
    {
        $queryBuilder = $this->createQueryBuilder('i')
            ->leftJoin("i.patient", "p")
            ->addSelect("p")
            ->where("i.statut = '". Inclusion::SCREEN ."'");
        $queryBuilder = $this->joinUserWhereUser($queryBuilder, $user);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param $id
     * @param User $user
     * @return array
     */
    public function findArray($id, User $user)
    {
        $queryBuilder = $this->createQueryBuilder('i')
            ->leftJoin('i.medecin', 'm')
            ->leftJoin('i.arc', 'a')
            ->leftJoin('i.visites', 'v')
            ->leftJoin('i.documents', 'd')
            ->leftJoin('i.service', 's')
            ->leftJoin('i.traitements', 't')
            ->leftJoin('i.eis', 'ei')
            ->leftJoin('i.events', 'ev')
            ->leftJoin('v.arc', 'va')
            ->addSelect('m','a', 'd', 'e', 'v', 's', 't', 'va', 'ei', "ev")
            ->where('i.id = :id')
            ->setParameter('id', $id);

        $queryBuilder = $this->joinUserWhereUser($queryBuilder, $user);

        $results = $queryBuilder->getQuery()->getArrayResult();

        foreach ($results as $key => $value) {
            if ($results[$key]["datCst"] != null)
                $results[$key]["datCst"] = $value["datCst"]->format('d/m/Y');
            if ($results[$key]["datInc"] != null)
                $results[$key]["datInc"] = $value["datInc"]->format('d/m/Y');
            if ($results[$key]["datJ0"] != null)
                $results[$key]["datJ0"] = $value["datJ0"]->format('d/m/Y');
            if ($results[$key]["datRan"] != null)
                $results[$key]["datRan"] = $value["datRan"]->format('d/m/Y');
            if ($results[$key]["datScr"] != null)
                $results[$key]["datScr"] = $value["datScr"]->format('d/m/Y');
            if ($results[$key]["datOut"] != null)
                $results[$key]["datOut"] = $value["datOut"]->format('d/m/Y');
            if ($results[$key]["medecin"] == null)
                $results[$key]["medecin"] = ["id" => null];
            if ($results[$key]["essai"] == null)
                $results[$key]["essai"] = ["id" => null];
            if ($results[$key]["arc"] == null)
                $results[$key]["arc"] = ["id" => null];
            if ($results[$key]["service"] == null)
                $results[$key]["service"] = ["id" => null];
            foreach ($results[$key]["visites"] as $key2 => $visite) {
                if ($visite["date"] != null)
                    $results[$key]["visites"][$key2]["date"] = $visite["date"]->format('d/m/Y H:i');
                if ($visite["date_fin"] != null)
                    $results[$key]["visites"][$key2]["date_fin"] = $visite["date_fin"]->format('d/m/Y H:i');
                if ($visite["arc"] == null)
                    $results[$key]["visites"][$key2]["arc"] = ["id" => null];
            }
        }
        if (!empty($results))
            return $results[0];

        return $results;
    }

    /**
     * @param $query
     * @param $filters
     * @param User $user
     * @return array
     */
    public function findAdvancedArray($query, $filters, User $user)
    {
        $queryBuilder = $this->createQueryBuilder('i')
            ->leftJoin('i.medecin', 'm')
            ->leftJoin('i.arc', 'a')
            ->leftJoin('i.service', 's')
            ->leftJoin('i.patient', 'p')
            ->addSelect('m', 'p', 'a', 'e', 's')
            ->groupBy("i.id")
            ->setMaxResults(500);

        $queryBuilder = $this->joinUserWhereUser($queryBuilder, $user);

        foreach ($query as $k => $q)
            if ($q != '')
                $queryBuilder->andWhere("i.numInc like :q$k or p.nom like :q$k or p.prenom like :q$k or e.nom like :q$k ")
                    ->setParameter("q$k", '%' . $q . "%");

        if (isset($filters["statut"]) && $filters["statut"] != null)
            $queryBuilder->andWhere("i.statut = :statut")
                ->setParameter("statut", $filters["statut"]);

        $queryBuilder->orderBy('i.numInc', 'ASC');

        return $queryBuilder->getQuery()->getArrayResult();
    }
}

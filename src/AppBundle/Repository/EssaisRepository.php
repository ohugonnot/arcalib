<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Essais;
use AppBundle\Entity\Inclusion;
use AppBundle\Entity\User;
use DateTime;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class EssaisRepository extends EntityRepository

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

    public function getQuery(User $user, $search)
    {
        $queryBuilder = $this->createQueryBuilder('e')
            ->select('COUNT(DISTINCT i) AS  nb', 'e')
            ->leftJoin('e.inclusions', 'i')
            ->addSelect("i")
            ->where("e.nom like :search or e.numeroInterne like :search or e.statut like :search or e.typeEssai like :search or e.typeProm like :search or e.dateOuv like :search or e.dateClose like :search")
            ->groupBy("e.id")
            ->setParameter('search', '%' . $search . '%');

        $queryBuilder = $this->joinUserWhereUser($queryBuilder, $user);

        return $queryBuilder->getQuery();
    }

    /**
     * @param $query
     * @param User $user
     * @return array
     */
    public function findByNomLike($query, User $user)
    {
        $queryBuilder = $this->createQueryBuilder('e')
            ->groupBy("e.id")
            ->setMaxResults(10);

        foreach ($query as $k => $q)
            $queryBuilder->andWhere("e.nom like :q$k")
                ->setParameter("q$k", '%' . $q . "%");

        $queryBuilder = $this->joinUserWhereUser($queryBuilder, $user);

        return $queryBuilder->getQuery()->getArrayResult();
    }

    /**
     * @param User $user
     * @return array
     */
    public function findNotArchived(User $user)
    {
        $queryBuilder = $this->createQueryBuilder('e')
            ->where("e.statut NOT IN ('" . Essais::ARCHIVE . "','" . Essais::REFUS . "') or e.statut is null")
            ->orderBy("e.nom", "ASC");

        $queryBuilder = $this->joinUserWhereUser($queryBuilder, $user);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param User $user
     * @return array
     */
    public function findAllByUser(User $user)
    {
        $queryBuilder = $this->createQueryBuilder('e');
        $queryBuilder = $this->joinUserWhereUser($queryBuilder, $user);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param User $user
     * @return array
     */
    public function findArchived(User $user)
    {
        $queryBuilder = $this->createQueryBuilder('e')
            ->where("e.statut IN('" . Essais::ARCHIVE . "','" . Essais::REFUS . "')")
            ->orderBy("e.nom", "ASC");

        $queryBuilder = $this->joinUserWhereUser($queryBuilder, $user);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param null|string $statut
     * @return array
     */
    public function findByStatut(?string $statut)
    {
        $queryBuilder = $this->createQueryBuilder('e')
            ->where("e.statut = :statut")
            ->setParameter('statut', $statut)
            ->orderBy("e.nom", "ASC");

        return $queryBuilder->getQuery()->getResult();
    }

    public function findActif()
    {
        $queryBuilder = $this->createQueryBuilder('e')
            ->where("e.statut IN ('" . Essais::INCLUSIONS_OUVERTES . "','" . Essais::INCLUSIONS_CLOSES_SUIVI . "')")
            ->orderBy("e.nom", "ASC");

        return $queryBuilder->getQuery()->getResult();
    }

    public function findEssaiEnAttente()
    {
        $queryBuilder = $this->createQueryBuilder('e')
            ->where("e.statut IN ('" . Essais::FAISABILITE_EN_ATTENTE . "','" . Essais::CONVENTION_SIGNATURE . "')")
            ->orderBy("e.nom", "ASC");

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param DateTime|null $debut
     * @param DateTime|null $fin
     * @return array
     */
    public function findEssaiByDateOuverture(?DateTime $debut, ?DateTime $fin = null)
    {
        if ($fin == null)
            $fin = date("Y-m-d H:i:s");

        $queryBuilder = $this->createQueryBuilder('e')
            ->where('e.dateOuv >= :debut')
            ->andwhere('e.dateOuv <= :fin')
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param DateTime|null $debut
     * @param DateTime|null $fin
     * @return array
     */
    public function findEssaiByDateCloture(?DateTime $debut, ?DateTime $fin = null)
    {
        if ($fin == null)
            $fin = date("Y-m-d H:i:s");

        $queryBuilder = $this->createQueryBuilder('e')
            ->where('e.dateClose >= :debut')
            ->andwhere('e.dateClose <= :fin')
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param $id
     * @param User $user
     * @return array
     */
    public function findArray($id, User $user)
    {
        $queryBuilder = $this->createQueryBuilder('e')
            ->leftJoin('e.services', 's')
            ->addSelect('s')
            ->leftJoin('e.medecin', 'm')
            ->addSelect('m')
            ->leftJoin('e.tags', 't')
            ->addSelect('t')
            ->leftJoin('e.arc', 'a')
            ->addSelect('a')
            ->leftJoin('e.arcBackup', 'ab')
            ->addSelect('ab')
            ->leftJoin('e.fils', 'fil')
            ->addSelect('fil')
            ->leftJoin('e.factures', 'f')
            ->addSelect('f')
            ->leftJoin('e.detail', 'd')
            ->addSelect('d')
            ->leftJoin('e.documents', 'doc')
            ->andWhere("e.id = :id")
            ->setParameter("id", $id);

        $queryBuilder = $this->joinUserWhereUser($queryBuilder, $user);

        $qb2 = $this->_em->createQueryBuilder()
            ->select("i, p, me")
            ->from(Inclusion::class, 'i')
            ->leftJoin('i.patient', 'p')
            ->leftJoin('i.medecin', 'me')
            ->andWhere('i.essai = :id')
            ->setParameter("id", $id);
        $qb2 = $this->joinUserWhereUser($qb2, $user);

        $results = $queryBuilder->getQuery()->getArrayResult();
        $inclusions = $qb2->getQuery()->getArrayResult();

        $results[0]['inclusions'] = $inclusions ?? [];

        if (empty($results))
            return $results;

        foreach ($results as $key => $value) {
            if ($results[$key]["dateOuv"] != null)
                $results[$key]["dateOuv"] = $value["dateOuv"]->format('d/m/Y');
            if ($results[$key]["dateFinInc"] != null)
                $results[$key]["dateFinInc"] = $value["dateFinInc"]->format('d/m/Y');
            if ($results[$key]["dateClose"] != null)
                $results[$key]["dateClose"] = $value["dateClose"]->format('d/m/Y');
            if ($results[$key]["dateSignConv"] != null)
                $results[$key]["dateSignConv"] = $value["dateSignConv"]->format('d/m/Y');
            if ($results[$key]["arc"] == null)
                $results[$key]["arc"] = ["id" => null];
            if ($results[$key]["arcBackup"] == null)
                $results[$key]["arcBackup"] = ["id" => null];
            if ($results[$key]["medecin"] == null)
                $results[$key]["medecin"] = ["id" => null];
            foreach ($results[$key]["inclusions"] as $k2 => $v2) {
                if ($v2["datInc"] === null)
                    continue;
                $results[$key]["inclusions"][$k2]["datInc"] = $v2["datInc"]->format('d/m/Y');
            }
            $services = [];
            foreach ($results[$key]["services"] as $k2 => $v2)
                $services[] = $v2["id"];
            $results[$key]["services"] = $services;
            foreach ($results[$key]["fils"] as $k2 => $v2)
                if ($v2["date"] != null)
                    $results[$key]["fils"][$k2]["date"] = $v2["date"]->format('d/m/Y');
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

        $queryBuilder = $this->createQueryBuilder('e')
            ->leftJoin('e.tags', 't')
            ->leftjoin('e.services', 's')
            ->orderBy('e.nom', 'ASC');

        if (isset($filters["statut"]) && $filters["statut"] != null)
            $queryBuilder->andWhere("e.statut = :statut")->setParameter("statut", $filters["statut"]);

        if (isset($filters["service"]) && $filters["service"] != '')
            $queryBuilder->andWhere("s.id = :serviceId")->setParameter("serviceId", $filters["service"]);

        $queryBuilderTest = clone $queryBuilder;

        $queryBuilderTest->groupBy("e.id")->setMaxResults(25);

        foreach ($query as $k => $q)
            if ($q != '')
                $queryBuilderTest->andWhere("e.nom like :q$k or e.objectif like :q$k or t.nom like :q$k or e.statut like :q$k")->setParameter("q$k", '%' . $q . "%");

        $results = $queryBuilderTest->getQuery()->getArrayResult();

        $essaiIds = [];
        foreach ($results as $essai)
            $essaiIds[] = $essai["id"];

        $queryBuilder
            ->leftJoin('e.arc', 'a')
            ->leftJoin('e.medecin', 'm')
            ->leftJoin('e.factures', 'f')
            ->leftJoin('e.detail', 'd')
            ->leftJoin('e.documents', 'doc')
            ->addSelect('m')
            ->addSelect('t')
            ->addSelect('a')
            ->addSelect('f')
            ->addSelect('d')
            ->addSelect('doc')
            ->andwhere("e.id IN(:essaiIds)")
            ->orderBy('e.id', 'ASC')
            ->setParameter('essaiIds', $essaiIds);

        $queryBuilder = $this->joinUserWhereUser($queryBuilder, $user);

        $qb2 = $this->_em->createQueryBuilder()
            ->select("i, p, e")
            ->from(Inclusion::class, 'i')
            ->leftJoin('i.essai', 'e')
            ->leftJoin('i.patient', 'p')
            ->andwhere("e.id IN(:essaiIds)")
            ->orderBy('e.id', 'ASC')
            ->setParameter("essaiIds", $essaiIds);
        $qb2 = $this->joinUserWhereUser($qb2, $user);

        $results = $queryBuilder->getQuery()->getArrayResult();
        $inclusions = $qb2->getQuery()->getArrayResult();

        foreach ($results as $k => $essai) {
            $essai['inclusions'] = [];
            foreach ($inclusions as $k2 => $inclusion) {
                if ($essai['id'] == $inclusion['essai']['id']) {
                    $essai['inclusions'][] = $inclusion;
                }
            }
            $results[$k] = $essai;
        }

        return $results;
    }

    public function findAllProtcoleJoinInclusion()
    {
        $queryBuilder = $this->createQueryBuilder('e')
            ->leftJoin('e.inclusions', 'i')
            ->addSelect("i")
            ->orderBy('e.id', 'DESC');

        return $queryBuilder->getQuery()->getResult();
    }
}

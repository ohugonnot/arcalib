<?php

namespace AppBundle\Repository;

use AppBundle\Entity\Inclusion;
use AppBundle\Entity\Patient;
use AppBundle\Entity\User;
use AppBundle\Entity\Visite;
use DateTime;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Exception;

class PatientRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param User $user
     * @return QueryBuilder
     */
    private function joinUserWhereUser(QueryBuilder $queryBuilder, User $user)
    {
        $queryBuilder->leftJoin('p.inclusions', 'i')
            ->leftJoin('i.essai', 'e');

        if (!$user->getEssais()->isEmpty() || $user->getRulesProtocole() == User::NO_PROTOCOLE)
            $queryBuilder
                ->leftJoin('e.users', 'u')
                ->andWhere("u = :user")
                ->setParameter("user", $user);

        return $queryBuilder;
    }

    /**
     * @param User $user
     * @param $search
     * @return Query
     */
    public function getQuery(User $user, $search)
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->select('p', 'COUNT(i) AS HIDDEN nbInclusions')
            ->addSelect("i")
            ->where("p.nom like :search or p.prenom like :search  or p.deces like :search or p.datDeces like :search or p.sexe like :search  or p.datNai like :search  or p.nomNaissance like :search or p.idInterne like :search")
            ->groupBy('p')
            ->setParameter('search', '%' . $search . '%');

        $queryBuilder = $this->joinUserWhereUser($queryBuilder, $user);

        return $queryBuilder->getQuery();
    }

    /**
     * @param $query
     * @param User $user
     * @return array
     */
    public function findByNomPrenom($query, User $user)
    {
        $query = explode(" ", $query);
        $queryBuilder = $this->createQueryBuilder('p')
            ->setMaxResults(10)
            ->groupBy('p');
        foreach ($query as $k => $q)
            $queryBuilder->andWhere("p.nom like :q$k or p.prenom like :q$k or p.nomNaissance like :q$k")
                ->setParameter("q$k", '%' . $q . "%");

        $queryBuilder = $this->joinUserWhereUser($queryBuilder, $user);

        return $queryBuilder->getQuery()->getArrayResult();
    }

    /**
     * @param User $user
     * @return mixed
     */
    public function findAllByUser(User $user)
    {
        $queryBuilder = $this->createQueryBuilder('p');
        $queryBuilder = $this->joinUserWhereUser($queryBuilder, $user);

        return $queryBuilder->getQuery()->getResult();
    }

// trouver patients dont la date de dernieres nouvelles est > 2 ans, et limiter à 100 sorties
    public function findPatientsDateNouvelle()
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->leftJoin('p.inclusions', 'i')
            ->leftJoin('i.essai', 'e')
            ->select("p", "e.nom", "e.statut", "e.id as id_essai", "i.id as id_inclusion")
            ->where("p.datLast <= :period and p.deces != '" . Patient::DECEDE . "'")
            ->setParameter('period', (new DateTime())->modify("-2 years"))
            ->orderBy("p.datLast", "DESC")
            ->setMaxResults(100);

        return $queryBuilder->getQuery()->getResult();
    }

    public function findPatientSuivis()
    {

        $queryBuilder = $this->createQueryBuilder('p')
            ->leftJoin('p.inclusions', 'i')
            ->where("i.statut = '" . Inclusion::OUI_EN_COURS . "'")
            ->orderBy("p.nom", "asc");

        return $queryBuilder->getQuery()->getResult();
    }

// trouver visite dans les 30 Hours
    /**
     * @return mixed
     * @throws Exception
     */
    public function findPatientVisite30Days()
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->leftJoin('p.inclusions', 'i')
            ->leftJoin('i.visites', 'v')
            ->leftJoin('i.essai', 'e')
            ->select("p", "e.nom", 'e.statut', "v.date", "v.date_fin", "e.id as id_essai", "i.id as id_inclusion")
            ->where("v.statut = '" . Visite::PREVUE_THEORIQUE . "' or v.statut = '" . Visite::PREVUE_CONFIRMEE . "'")
            ->andWhere('v.date BETWEEN :now and :period ')
            ->setParameter('period', (new DateTime())->modify("+30 days"))
            ->setParameter('now', (new DateTime()))
            ->orderBy("v.date", "DESC");

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param int $id
     * @param User $user
     * @return array
     */
    public function findArray(int $id, User $user)
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->leftJoin('p.medecin', 'm')
            ->leftJoin('p.libCim10', 'l')
            ->addSelect('m')
            ->addSelect('l')
            ->addSelect('i')
            ->addSelect('e')
            ->andWhere("p.id = :id")
            ->setParameter("id", $id);

        $queryBuilder = $this->joinUserWhereUser($queryBuilder, $user);

        $results = $queryBuilder->getQuery()->getArrayResult();

        foreach ($results as $key => $value) {
            if ($results[$key]["datNai"] != null)
                $results[$key]["datNai"] = $value["datNai"]->format('d/m/Y');
            if ($results[$key]["datDiag"] != null)
                $results[$key]["datDiag"] = $value["datDiag"]->format('d/m/Y');
            if ($results[$key]["datLast"] != null)
                $results[$key]["datLast"] = $value["datLast"]->format('d/m/Y');
            if ($results[$key]["datDeces"] != null)
                $results[$key]["datDeces"] = $value["datDeces"]->format('d/m/Y');
            if ($results[$key]["medecin"] == null)
                $results[$key]["medecin"] = ["id" => null];
            if ($results[$key]["libCim10"] == null)
                $results[$key]["libCim10"] = ["id" => null];
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
        $query = explode(" ", $query);

        $queryBuilder = $this->createQueryBuilder('p')
            ->leftJoin('p.medecin', 'm')
            ->leftJoin('p.libCim10', 'l')
            ->addSelect('m')
            ->addSelect('l')
            ->addSelect('i')
            ->addSelect('e')
            ->orderBy('p.nom', 'ASC');;

        $queryBuilder = $this->joinUserWhereUser($queryBuilder, $user);

        $queryBuilderTest = clone $queryBuilder;
        $queryBuilderTest->groupBy("p.id")->setMaxResults(25);

        foreach ($query as $k => $q)
            if ($q != '')
                $queryBuilderTest->andWhere("p.nom like :q$k or p.prenom like :q$k or p.nomNaissance like :q$k or p.datNai like :q$k")
                    ->setParameter("q$k", '%' . $q . "%");

        if (isset($filters["statut"]) && $filters["statut"] != null)
            $queryBuilderTest->andWhere("i.statut = :statut")
                ->setParameter("statut", $filters["statut"]);

        $results = $queryBuilderTest->getQuery()->getArrayResult();

        $ids = [];
        foreach ($results as $patient)
            $ids[] = $patient["id"];

        $queryBuilder
            ->andwhere("p.id IN(:ids)")
            ->setParameter('ids', $ids);

        $results = $queryBuilder->getQuery()->getArrayResult();

        return $results;
    }

// eviter doublon patients
    /**
     * @param array $array
     * @return bool
     */
    public function alreadyExist(array $array)
    {
        $queryBuilder = $this->createQueryBuilder('p')
            ->andWhere('p.datNai LIKE :datNai')
            ->setParameter('datNai', $array["datNai"]->format("Y-m-d") . "%")
            ->andWhere('p.nom = :nom')
            ->setParameter('nom', $array["nom"])
            ->andWhere('p.prenom = :prenom')
            ->setParameter('prenom', $array["prenom"]);

        $results = $queryBuilder->getQuery()->getResult();

        return !empty($results);
    }
}

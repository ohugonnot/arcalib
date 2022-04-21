<?php

namespace AppBundle\Repository;

use AppBundle\Entity\User;
use AppBundle\Entity\Visite;
use DateInterval;
use DateTime;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\Query;
use Doctrine\ORM\QueryBuilder;
use Exception;

class VisiteRepository extends EntityRepository
{
    /**
     * @param QueryBuilder $queryBuilder
     * @param User $user
     * @return QueryBuilder
     */
    private function joinUserWhereUser(QueryBuilder $queryBuilder, User $user)
    {
        $queryBuilder->leftJoin('v.inclusion', 'i')
            ->leftJoin('i.arc', 'a')
            ->leftJoin('i.essai', 'e')
            ->leftJoin('i.patient', 'p');

        if (!$user->getEssais()->isEmpty() || $user->getRulesProtocole() == User::NO_PROTOCOLE)
            $queryBuilder->andWhere("u = :user")
                ->setParameter("user", $user)
                ->leftJoin('e.users', 'u');

        return $queryBuilder;
    }

    /**
     * @param User $user
     * @param $search
     * @param $searchId
     * @return Query
     */
    public function getQuery(User $user, $search, $searchId)
    {
        $queryBuilder = $this->createQueryBuilder('v')
            ->where("v.id like :search or p.nom like :search or p.prenom like :search or e.nom like :search or v.date like :search or v.statut like :search or v.type like :search or v.calendar like :search or v.id = :searchId")
            ->addSelect("e","a","p","i")
            ->groupBy('v.id')
            ->setParameter('searchId', $searchId)
            ->setParameter('search', '%' . $search . '%');

        $queryBuilder = $this->joinUserWhereUser($queryBuilder, $user);

        return $queryBuilder->getQuery();
    }

    /**
     * @param DateTime|null $debut
     * @param DateTime|null $fin
     * @return array
     */
    public function findByDate(?DateTime $debut, ?DateTime $fin = null, $user = null)
    {
        if ($fin == null)
            $fin = date("Y-m-d H:i:s");

        $queryBuilder = $this->createQueryBuilder('v')
            ->andWhere('v.date BETWEEN :debut AND :fin or v.date_fin BETWEEN :debut AND :fin or (v.date <= :debut and v.date_fin >= :fin)')
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin);

        if ($user){
            $queryBuilder = $this->joinUserWhereUser($queryBuilder, $user);
        }

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param $user
     * @return Visite[]
     * @throws Exception
     */
    public function findForAWeek(User $user)
    {
        $debut = (new DateTime())->setTime(0, 0);
        $now = (new DateTime())->setTime(0, 0);
        $interval = new DateInterval('P1W');
        $fin = $now->add($interval);

        $queryBuilder = $this->createQueryBuilder('v')
            ->addSelect("e", "a", "i","p")
            ->where('v.date >= :debut')
            ->andWhere('v.date <= :fin')
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
            ->orderBy('v.date', "ASC");

        $queryBuilder = $this->joinUserWhereUser($queryBuilder, $user);

        return $queryBuilder->getQuery()->getResult();
    }

    public function findConfirmeeTheoriqueDepassee($user)
    {
        $queryBuilder = $this->createQueryBuilder('v')
            ->addSelect("e", "a", "i","p")
            ->andWhere('v.date < :today')
            ->andWhere("v.statut = '". Visite::PREVUE_CONFIRMEE ."' or v.statut = '". Visite::PREVUE_THEORIQUE ."'")
            ->setParameter('today', new DateTime("today"))
            ->orderBy('v.date', "ASC");

        $queryBuilder = $this->joinUserWhereUser($queryBuilder, $user);

        return $queryBuilder->getQuery()->getResult();
    }

    /**
     * @param DateTime|null $date
     * @param array $filters
     * @param User $user
     * @return array
     */
    public function findAdvancedArray(?Datetime $date, array $filters, User $user)
    {
        $queryBuilder = $this->createQueryBuilder('v')
            ->addSelect('p', 'a', 'e', 'i')
            ->groupBy("v.id")
            ->setMaxResults(500);

        $queryBuilder = $this->joinUserWhereUser($queryBuilder, $user);

        /** @var DateTime $date */
        if ($date)
            $queryBuilder->andWhere("YEAR(v.date) = :year")
                ->setParameter("year", $date->format("Y"))
                ->andWhere("MONTH(v.date) = :month")
                ->setParameter("month", $date->format("m") )
                ->andWhere("DAY(v.date) = :day")
                ->setParameter("day", $date->format("d") );

        if (isset($filters["statut"]) && $filters["statut"] != null)
            $queryBuilder->andWhere("v.statut = :statut")
                ->setParameter("statut", $filters["statut"]);

        $queryBuilder->orderBy('v.date', 'ASC');

        return $queryBuilder->getQuery()->getArrayResult();
    }
}

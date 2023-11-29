<?php

namespace AppBundle\Repository;

use DateTime;
use Doctrine\ORM\EntityRepository;

class LogRepository extends EntityRepository
{
    public function removeBadLog()
    {
        $queryBuilder = $this->createQueryBuilder('l')
            ->delete('AppBundle:log', 'l')
            ->andWhere("l.action = 'edition' and l.entity = 'Visite' and l.createdAt >= :month")
            ->setParameter('month', (new DateTime())->modify("-6 month"));

        return $queryBuilder->getQuery()->execute();
    }
}

<?php

namespace AppBundle\Services;

use AppBundle\Entity\Visite;
use Doctrine\ORM\EntityManagerInterface;

class DeplaceVisiteNonFait
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function deplaceVisite()
    {
        $emVisite = $this->em->getRepository(Visite::class);
        $visiteNonFaiteLastWeek = $emVisite->findVisiteNonFaiteLastWeek();
        foreach ($visiteNonFaiteLastWeek as $visite) {

            // garder la date original au premier changement de date et ne plus jamais les changé une fois qu'elle est rempli
            if ($visite->getDateOriginal() == null) {
                $date_debut_original = clone $visite->getDate();
                $visite->setDateOriginal($date_debut_original);
            }

            /** @var Visite $visite */
            if ($visite->getDate())
                $date_debut = clone $visite->getDate()->setDate(date("Y"), date("m"), date("d"));
            else
                $date_debut = $visite->getDate();
            if ($visite->getDateFin())
                $date_fin = clone $visite->getDateFin()->setDate(date("Y"), date("m"), date("d"));
            else
                $date_fin = $visite->getDateFin();

            $visite->setDate($date_debut);
            $visite->setDateFin($date_fin);
            $this->em->persist($visite);
        }
        $this->em->flush();
    }
}
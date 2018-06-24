<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Essais;
use AppBundle\Entity\Inclusion;
use AppBundle\Entity\Visite;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/arcalib")
 */
class AnalyseController extends Controller
{

// ------------------------------------------ANALYSE GRAPHIQUE-----------------------------------------------------  
    /**
     * @Route("/analyse/graphiques", name="analyseGraphique")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function analyseGraphiqueAction(Request $request)
    {
        $inclusionMedecin = $this->inclusionByMedecin();
        $inclusionByYear = $this->inclusionByYear();
        $lastYear = new \DateTime();
        $lastYear->modify('-12 month');
        $endMonth = new \DateTime();
        $endMonth->modify('last day of this month');

        if ($request->isMethod('POST')) {

            $debut = \DateTime::createFromFormat("d/m/Y", $request->get("dateDebut"));
            $fin = \DateTime::createFromFormat("d/m/Y", $request->get("dateFin"));

            $dateMedecin = $dateProtocole = $dateVisites = $inclusionsService = $inclusionsMedecin = null;

            if ($fin && $debut) {
                $dateMedecin = $this->inclusionMedecinByDate($debut, $fin);
                $dateProtocole = $this->inclusionProtocoleByDate($debut, $fin);
                $dateVisites = $this->visiteProtocoleByDate($debut, $fin);
                $inclusionsService = $this->inclusionsByService($debut, $fin);
                $inclusionsMedecin = $this->inclusionByMedecin($debut, $fin);
                $inclusionsByProtocole = $this->inclusionsByProtocole($debut, $fin);
            }
        } else {
            $dateMedecin = $this->inclusionMedecinByDate($lastYear, $endMonth);
            $dateProtocole = $this->inclusionProtocoleByDate($lastYear, $endMonth);
            $dateVisites = $this->visiteProtocoleByDate($lastYear, $endMonth);
            $inclusionsService = $this->inclusionsByService($lastYear, $endMonth);
            $inclusionsMedecin = $this->inclusionByMedecin($lastYear, $endMonth);
            $inclusionsByProtocole = $this->inclusionsByProtocole($lastYear, $endMonth);
        }

        return $this->render('analyse/graphiques.html.twig', [
            "inclusionMedecin" => $inclusionMedecin,
            'inclusionsByMounthMedecin' => $dateMedecin,
            'inclusionsByMounthProtocole' => $dateProtocole,
            'visiteByMounthProtocole' => $dateVisites,
            'inclusionsService' => $inclusionsService,
            'inclusionsMedecin' => $inclusionsMedecin,
            'inclusionsByProtocole' => $inclusionsByProtocole ?? null,
            'inclusionByYear' => $inclusionByYear,
            'debut' => isset($debut) ? $debut : $lastYear,
            "fin" => isset($fin) ? $fin : $endMonth,
        ]);
    }

    public function inclusionByMedecin(?\DateTime $debut = null, ?\DateTime $fin = null)
    {
        $em = $this->getDoctrine()->getManager();

        if (!$debut && !$fin) {
            $inclusions = $em->getRepository(Inclusion::class)
                ->createQueryBuilder('i')
                ->select('i')
                ->join('i.medecin', 'm')
                ->addSelect("m")
                ->orderBy('m.nom')
                ->getQuery()
                ->getResult();

        } else {
            /** @var Inclusion[] $inclusions */
            $inclusions = $em->getRepository(Inclusion::class)
                ->createQueryBuilder('i')
                ->select('i')
                ->where('i.datInc IS NOT NULL')
                ->andWhere('i.datInc >= :debut')
                ->andWhere('i.datInc <= :fin')
                ->setParameter('debut', $debut)
                ->setParameter('fin', $fin)
                ->join('i.medecin', 'm')
                ->addSelect("m")
                ->orderBy('m.nom')
                ->getQuery()
                ->getResult();
        }

        $inclusionMedecin = [];

        foreach ($inclusions as $inclusion) {

            $medecin = $inclusion->getMedecin();
            if (!$medecin) {
                continue;
            }
            if (!isset($inclusionMedecin[$medecin->getNom() . ' ' . substr($medecin->getPrenom(), 0, 1) . '.'])) {
                $inclusionMedecin[$medecin->getNom() . ' ' . substr($medecin->getPrenom(), 0, 1) . '.'] = 0;
            }
            $inclusionMedecin[$medecin->getNom() . ' ' . substr($medecin->getPrenom(), 0, 1) . '.'] += 1;
        }

        arsort($inclusionMedecin);
        return $inclusionMedecin;
    }

// Creation du vecteurs medecin, date, inclusion-CANVAS 2------------------------------------------------------------------------------

    public function inclusionByYear()
    {
        $em = $this->getDoctrine()->getManager();

        $inclusionsByYear = $em->getRepository(Inclusion::class)
            ->createQueryBuilder('i')
            ->select(' count(i) as nb, YEAR(i.datInc) AS year')
            ->where('i.datInc IS NOT NULL')
            ->orderBy('i.datInc')
            ->groupBy('year')
            ->getQuery()
            ->getResult();

        return $inclusionsByYear;
    }

// Creation du vecteurs medecin, date, inclusion--CANVAS 3-----------------------------------------------------------------------------

    public function inclusionMedecinByDate(?\DateTime $debut, ?\DateTime $fin)
    {
        if (!$debut && !$fin) {
            return false;
        }

        $em = $this->getDoctrine()->getManager();

        $inclusionsByMonth = $em->getRepository(Inclusion::class)
            ->createQueryBuilder('i')
            ->select(' count(i) as nb, MONTH(i.datInc) AS month, YEAR(i.datInc) AS year, m.nom as nom, m.prenom as prenom')
            ->join('i.medecin', 'm')
            ->where('i.datInc IS NOT NULL')
            ->andWhere('i.datInc >= :debut')
            ->andWhere('i.datInc <= :fin')
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
            ->orderBy('i.datInc, m.nom')
            ->groupBy('month')
            ->addGroupBy('year')
            ->addGroupBy('i.medecin')
            ->getQuery()
            ->getResult();

        $dateMedecin = [];

        // Creation du vecteurs medecin, date, inclusion
        foreach ($inclusionsByMonth as $array) {

            $formtedMonth = $this->add0($array["month"]);
            $year = $array["year"];
            $nom = $array["nom"] . ' ' . substr($array["prenom"], 0, 1) . '.';

            if (!isset($dateMedecin[$nom][$year . $formtedMonth])) {
                $dateMedecin[$nom][$year . $formtedMonth] = [];
            }

            $dateMedecin[$nom][$year . $formtedMonth] = $array;
        }

        // Création du vecteur par defaut avec les inclusions a 0
        for ($annee = $debut->format("Y"); $annee <= $fin->format("Y"); $annee++) {

            for ($month = 1; $month <= 12; $month++) {

                $formtedMonth = $this->add0($month);

                foreach ($dateMedecin as $key => $value) {

                    if ($debut->format("Y") == $annee && $month < (int)$debut->format("m") - 1) {
                        continue;
                    }
                    if ($fin->format("Y") == $annee && $month > (int)$fin->format("m") + 1) {
                        continue;
                    }
                    if (isset($dateMedecin[$key][$annee . $formtedMonth])) {
                        continue;
                    }

                    $dateMedecin[$key][$annee . $formtedMonth] = ["nb" => 0, "month" => $formtedMonth, "year" => $annee];
                }
            }
        }

        ksort($dateMedecin);
        return $dateMedecin;
    }

    public function add0($month)
    {

        return str_pad($month, 2, '0', STR_PAD_LEFT);
    }

// Inclusion par médecin----CANVAS 1---------------------------------------------------------------------------

    public function inclusionProtocoleByDate(?\DateTime $debut, ?\DateTime $fin)
    {
        if (!$debut && !$fin) {
            return false;
        }

        $em = $this->getDoctrine()->getManager();

        $inclusionsByMonth = $em->getRepository(Inclusion::class)
            ->createQueryBuilder('i')
            ->select(' count(i) as nb, MONTH(i.datInc) AS month, YEAR(i.datInc) AS year, e.nom as nom')
            ->join('i.essai', 'e')
            ->where('i.datInc IS NOT NULL')
            ->andWhere('i.datInc >= :debut')
            ->andWhere('i.datInc <= :fin')
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
            ->orderBy('i.datInc, e.nom')
            ->groupBy('month')
            ->addGroupBy('year')
            ->addGroupBy('i.essai')
            ->getQuery()
            ->getResult();

        $dateProtocole = [];

        // Creation du vecteurs medecin, date, inclusion
        foreach ($inclusionsByMonth as $array) {

            $formattedByMonth = $this->add0($array["month"]);
            $year = $array["year"];
            $nom = $array["nom"];

            if (!isset($dateProtocole[$nom][$year . $formattedByMonth])) {
                $dateProtocole[$nom][$year . $formattedByMonth] = [];
            }

            $dateProtocole[$nom][$year . $formattedByMonth] = $array;
        }

        // Création du vecteur par defaut avec les inclusions a 0
        for ($annee = $debut->format("Y"); $annee <= $fin->format("Y"); $annee++) {

            for ($month = 1; $month <= 12; $month++) {

                $formattedByMonth = $this->add0($month);

                foreach ($dateProtocole as $key => $value) {

                    if ($debut->format("Y") == $annee && $month < (int)$debut->format("m") - 1) {
                        continue;
                    }
                    if ($fin->format("Y") == $annee && $month > (int)$fin->format("m") + 1) {
                        continue;
                    }
                    if (isset($dateProtocole[$key][$annee . $formattedByMonth])) {
                        continue;
                    }

                    $dateProtocole[$key][$annee . $formattedByMonth] = ["nb" => 0, "month" => $formattedByMonth, "year" => $annee];
                }
            }
        }

        ksort($dateProtocole);
        return $dateProtocole;
    }

    public function visiteProtocoleByDate(?\DateTime $debut, ?\DateTime $fin)
    {
        if (!$debut && !$fin) {
            return false;
        }

        $em = $this->getDoctrine()->getManager();

        $visitesByMonth = $em->getRepository(Visite::class)
            ->createQueryBuilder('v')
            ->select(' count(v) as nb, MONTH(v.date) AS month, YEAR(v.date) AS year, e.nom as nom')
            ->leftJoin('v.inclusion', 'i')
            ->join('i.essai', 'e')
            ->where('v.date IS NOT NULL')
            ->andWhere('v.date >= :debut')
            ->andWhere('v.date <= :fin')
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
            ->orderBy('v.date, e.nom')
            ->groupBy('month')
            ->addGroupBy('year')
            ->addGroupBy('i.essai')
            ->getQuery()
            ->getResult();

        $dateVisites = [];

        // Creation du vecteurs medecin, date, inclusion
        foreach ($visitesByMonth as $array) {

            $formtedMonth = $this->add0($array["month"]);
            $year = $array["year"];
            $nom = $array["nom"];

            if (!isset($dateVisites[$nom][$year . $formtedMonth])) {
                $dateVisites[$nom][$year . $formtedMonth] = [];
            }

            $dateVisites[$nom][$year . $formtedMonth] = $array;
        }

        // Création du vecteur par defaut avec les inclusions a 0
        for ($annee = $debut->format("Y"); $annee <= $fin->format("Y"); $annee++) {

            for ($month = 1; $month <= 12; $month++) {

                $formtedMonth = $this->add0($month);

                foreach ($dateVisites as $key => $value) {

                    if ($debut->format("Y") == $annee && $month < (int)$debut->format("m") - 1) {
                        continue;
                    }
                    if ($fin->format("Y") == $annee && $month > (int)$fin->format("m") + 1) {
                        continue;
                    }
                    if (isset($dateVisites[$key][$annee . $formtedMonth])) {
                        continue;
                    }

                    $dateVisites[$key][$annee . $formtedMonth] = ["nb" => 0, "month" => $formtedMonth, "year" => $annee];
                }
            }
        }

        ksort($dateVisites);
        return $dateVisites;
    }

// Inclusion par service/ mois---cenvas 5------------------------------------------------------------------------

    public function inclusionsByService(?\DateTime $debut, ?\DateTime $fin)
    {
        if (!$debut && !$fin) {
            return false;
        }

        $em = $this->getDoctrine()->getManager();

        $inclusionsByMounth = $em->getRepository(Inclusion::class)
            ->createQueryBuilder('i')
            ->select(' count(i) as nb, s.nom as serviceNom')
            ->join('i.essai', 'e')
            ->join('i.service', 's')
            ->where('i.datInc IS NOT NULL')
            ->andWhere('i.datInc >= :debut')
            ->andWhere('i.datInc <= :fin')
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
            ->orderBy('serviceNom')
            ->groupBy('serviceNom')
            ->getQuery()
            ->getResult();

        return $inclusionsByMounth;
    }


    public function inclusionsByProtocole(?\DateTime $debut, ?\DateTime $fin)
    {
        if (!$debut && !$fin) {
            return false;
        }

        $em = $this->getDoctrine()->getManager();

        $inclusionsByProtocole = $em->getRepository(Inclusion::class)
            ->createQueryBuilder('i')
            ->select(' count(i) as nb, e.nom as protocoleNom')
            ->join('i.essai', 'e')
            ->where('i.datInc IS NOT NULL')
            ->andWhere('i.datInc >= :debut')
            ->andWhere('i.datInc <= :fin')
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
            ->orderBy('protocoleNom')
            ->groupBy('protocoleNom')
            ->getQuery()
            ->getResult();

        return $inclusionsByProtocole;
    }

    /**
     * @Route("/analyse/tableau", name="analyseTableau")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function analyseTableauAction()
    {
        $em = $this->getDoctrine()->getManager();

        /** @var Essais[] $essais */
        $essais = $em->getRepository(Essais::class)->findAllProtcoleJoinInclusion();
        $statuts = Essais::STATUT;
        $essaisStatistique = [];

        foreach ($statuts as $statut) {
            $essaisStatistique[$statut] = 0;
        }

        unset($statuts[Essais::AUTRE]);
        unset($statuts[Essais::REFUS]);
        unset($statuts[Essais::ARCHIVE]);

        foreach ($essais as $essai) {
            ++$essaisStatistique[$essai->getStatut()];
        }

        return $this->render('analyse/tableau.html.twig', [
            "essais" => $essais,
            "statuts" => $statuts,
            "essaisStatistique" => $essaisStatistique,
        ]);
    }
}

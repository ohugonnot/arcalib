<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Essais;
use AppBundle\Entity\Inclusion;
use AppBundle\Entity\Visite;
use DateTime;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * TODO REFACTO LE CONTROLLEUR EN MODE SERVICE
 * @Route("/arcalib")
 */
class AnalyseController extends Controller
{

// ------------------------------------------ANALYSE GRAPHIQUE-----------------------------------------------------  
    /**
     * @Route("/analyse/graphiques", name="analyseGraphique")
     * @param Request $request
     * @return Response
     * @throws Exception
     */
    public function analyseGraphiqueAction(Request $request)
    {
        $inclusionMedecin = $this->inclusionByMedecin();
        $inclusionByYear = $this->inclusionByYear();
        $inclusionByYearByTypeEssai = $this->inclusionByYearByTypeEssai();
        $inclusionByYearByTypeProm = $this->inclusionByYearByTypeProm();
        $debut = new DateTime();
        $debut->modify('-12 month');
        $fin = new DateTime();
        $fin->modify('last day of this month');

        if ($request->isMethod('POST')) {
            $debut_request = DateTime::createFromFormat("d/m/Y", $request->get("dateDebut"));
            $fin_request = DateTime::createFromFormat("d/m/Y", $request->get("dateFin"));
            if ($debut_request && $fin_request) {
                $debut = $debut_request;
                $fin = $fin_request;
            }
        }

        $dateMedecin = $this->inclusionMedecinByDate($debut, $fin);
        $dateArc = $this->inclusionArcByDate($debut, $fin);
        $dateProtocole = $this->inclusionProtocoleByDate($debut, $fin);
        $dateVisites = $this->visiteProtocoleByDate($debut, $fin);
        $dateArcVisites = $this->visiteArcByDate($debut, $fin);
        $inclusionsService = $this->inclusionsByService($debut, $fin);
        $inclusionsArc = $this->inclusionByArc($debut, $fin);
        $inclusionsMedecin = $this->inclusionByMedecin($debut, $fin, false);
        $inclusionsByProtocole = $this->inclusionsByProtocole($debut, $fin);
        $inclusionByTypeEssai = $this->inclusionByTypeEssai($debut, $fin);
        $inclusionByTypeProm = $this->inclusionByTypeProm($debut, $fin);

        return $this->render('analyse/graphiques.html.twig', [
            "inclusionMedecin" => $inclusionMedecin,
            'inclusionsByMounthMedecin' => $dateMedecin,
            'inclusionsByMounthArc' => $dateArc,
            'inclusionsByMounthProtocole' => $dateProtocole,
            'visiteByMounthProtocole' => $dateVisites,
            'visiteByMounthArc' => $dateArcVisites,
            'inclusionsService' => $inclusionsService,
            'inclusionsMedecin' => $inclusionsMedecin,
            'inclusionsArc' => $inclusionsArc,
            'inclusionsByProtocole' => $inclusionsByProtocole ?? null,
            'inclusionByYear' => $inclusionByYear,
            'inclusionByTypeEssai' => $inclusionByTypeEssai,
            'inclusionByTypeProm' => $inclusionByTypeProm,
            'inclusionByYearByTypeEssai' => $inclusionByYearByTypeEssai,
            'inclusionByYearByTypeProm' => $inclusionByYearByTypeProm,
            'debut' => $debut,
            "fin" => $fin,
        ]);
    }

    public function inclusionByMedecin(?DateTime $debut = null, ?DateTime $fin = null, $order = false): array
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
        if ($order) {
            ksort($inclusionMedecin);
        } else {
            arsort($inclusionMedecin);
        }

        return $inclusionMedecin;
    }

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

// Ong2-CANVAS 2-Creation du vecteurs medecin, date, inclusion-CANVAS 2------------------------------------------------------------------------------

    private function inclusionByYearByTypeEssai()
    {
        $em = $this->getDoctrine()->getManager();

        $inclusionByYearByTypeEssai = $em->getRepository(Inclusion::class)
            ->createQueryBuilder('i')
            ->select(' count(i) as nb, YEAR(i.datInc) AS year, e.typeEssai as type')
            ->join('i.essai', 'e')
            ->where('i.datInc IS NOT NULL')
            ->groupBy('year, type')
            ->orderBy('year')
            ->getQuery()
            ->getResult();

        $data = [];
        $years = [];
        $essais = [];
        foreach ($inclusionByYearByTypeEssai as $resp) {
            if (empty($data[$resp['year']][$resp['type']]))
                $data[$resp['year']][$resp['type']] = 0;
            $data[$resp['year']][$resp['type']] += $resp['nb'];
            $years[] = $resp['year'];
            $essais[] = $resp['type'];
        }
        $years = array_unique($years);
        $essais = array_unique($essais);
        sort($years);
        sort($essais);
        foreach ($data as $year => $v) {
            $total = 0;
            foreach ($v as $nb) {
                $total += $nb;
            }
            foreach ($v as $name => $nb) {
                $data[$year][$name] = $data[$year][$name] / $total * 100;
            }
        }

        $data_by_type = [];
        foreach ($years as $year) {
            foreach ($essais as $v) {
                if (empty($data[$year][$v]))
                    $data_by_type[$v][] = 0;
                else
                    $data_by_type[$v][] = $data[$year][$v];
            }
        }

        return ["years" => $years, "data" => $data_by_type];
    }

    private function inclusionByYearByTypeProm()
    {
        $em = $this->getDoctrine()->getManager();

        $inclusionByYearByTypeEssai = $em->getRepository(Inclusion::class)
            ->createQueryBuilder('i')
            ->select(' count(i) as nb, YEAR(i.datInc) AS year, e.typeProm as type')
            ->join('i.essai', 'e')
            ->where('i.datInc IS NOT NULL')
            ->groupBy('year, type')
            ->orderBy('year')
            ->getQuery()
            ->getResult();

        $data = [];
        $years = [];
        $essais = [];
        foreach ($inclusionByYearByTypeEssai as $resp) {
            if (empty($data[$resp['year']][$resp['type']]))
                $data[$resp['year']][$resp['type']] = 0;
            $data[$resp['year']][$resp['type']] += $resp['nb'];
            $years[] = $resp['year'];
            $essais[] = $resp['type'];
        }
        $years = array_unique($years);
        $essais = array_unique($essais);
        sort($years);
        sort($essais);
        foreach ($data as $year => $v) {
            $total = 0;
            foreach ($v as $nb) {
                $total += $nb;
            }
            foreach ($v as $name => $nb) {
                $data[$year][$name] = $data[$year][$name] / $total * 100;
            }
        }

        $data_by_type = [];
        foreach ($years as $year) {
            foreach ($essais as $v) {
                if (empty($data[$year][$v]))
                    $data_by_type[$v][] = 0;
                else
                    $data_by_type[$v][] = $data[$year][$v];
            }
        }

        return ["years" => $years, "data" => $data_by_type];
    }

// Ong1-CANVAS 3-Creation du vecteurs medecin, date, inclusion--CANVAS 3-----------------------------------------------------------------------------

    public function inclusionMedecinByDate(?DateTime $debut, ?DateTime $fin)
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

// Ong3-CANVAS 11-Creation du vecteur Arc entre 2 dates-----------------------------------------------------------------------------

    public function add0(int $month): string
    {

        return str_pad($month, 2, '0', STR_PAD_LEFT);
    }

    public function inclusionArcByDate(?DateTime $debut, ?DateTime $fin)
    {
        if (!$debut && !$fin) {
            return false;
        }

        $em = $this->getDoctrine()->getManager();

        $inclusionsByMonth = $em->getRepository(Inclusion::class)
            ->createQueryBuilder('i')
            ->select('count(i) as nb, MONTH(i.datInc) AS month, YEAR(i.datInc) AS year, a.nomArc as nom, a.prenomArc as prenom')
            ->join('i.arc', 'a')
            ->where('i.datInc IS NOT NULL')
            ->andWhere('i.datInc >= :debut')
            ->andWhere('i.datInc <= :fin')
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
            ->orderBy('i.datInc, a.nomArc, a.prenomArc')
            ->groupBy('month')
            ->addGroupBy('year')
            ->addGroupBy('i.arc')
            ->getQuery()
            ->getResult();

        $dateArc = [];

        // Creation du vecteurs arc, date, inclusion
        foreach ($inclusionsByMonth as $array) {
            $formtedMonth = $this->add0($array["month"]);
            $year = $array["year"];
            $nom = $array["nom"] . ' ' . $array["prenom"];
            if (!isset($dateArc[$nom][$year . $formtedMonth])) {
                $dateArc[$nom][$year . $formtedMonth] = [];
            }
            $dateArc[$nom][$year . $formtedMonth] = $array;
        }

        // Création du vecteur par defaut avec les inclusions a 0
        for ($annee = $debut->format("Y"); $annee <= $fin->format("Y"); $annee++) {
            for ($month = 1; $month <= 12; $month++) {
                $formtedMonth = $this->add0($month);
                foreach ($dateArc as $key => $value) {
                    if ($debut->format("Y") == $annee && $month < (int)$debut->format("m") - 1) {
                        continue;
                    }
                    if ($fin->format("Y") == $annee && $month > (int)$fin->format("m") + 1) {
                        continue;
                    }
                    if (isset($dateArc[$key][$annee . $formtedMonth])) {
                        continue;
                    }
                    $dateArc[$key][$annee . $formtedMonth] = ["nb" => 0, "month" => $formtedMonth, "year" => $annee];
                }
            }
        }

        ksort($dateArc);
        return $dateArc;
    }

// Ong1-CANVAS ?-Inclusion par protocole-------------------------------------------------------------------------------

    public function inclusionProtocoleByDate(?DateTime $debut, ?DateTime $fin)
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

        return $this->orderByDate($inclusionsByMonth, $debut, $fin);
    }

    /**
     * @param $valuesByMonth
     * @param $debut DateTime
     * @param $fin DateTime
     * @return array
     */
    private function orderByDate($valuesByMonth, $debut, $fin)
    {
        $date = [];

        foreach ($valuesByMonth as $array) {
            $formattedByMonth = $this->add0($array["month"]);
            $year = $array["year"];
            $nom = $array["nom"] . ' ' . ($array["prenom"] ?? null);
            if (!isset($date[$nom][$year . $formattedByMonth])) {
                $date[$nom][$year . $formattedByMonth] = [];
            }
            $date[$nom][$year . $formattedByMonth] = $array;
        }

        // Création du vecteur par defaut avec les inclusions a 0
        for ($annee = $debut->format("Y"); $annee <= $fin->format("Y"); $annee++) {
            for ($month = 1; $month <= 12; $month++) {
                $formattedByMonth = $this->add0($month);
                foreach ($date as $key => $value) {
                    if ($debut->format("Y") == $annee && $month < (int)$debut->format("m") - 1) {
                        continue;
                    }
                    if ($fin->format("Y") == $annee && $month > (int)$fin->format("m") + 1) {
                        continue;
                    }
                    if (isset($date[$key][$annee . $formattedByMonth])) {
                        continue;
                    }
                    $date[$key][$annee . $formattedByMonth] = ["nb" => 0, "month" => $formattedByMonth, "year" => $annee];
                }
            }
        }

        ksort($date);
        return $date;
    }

// Ong4-CANVAS 4-visite/Protocole/date----------------------------------------------------------------------------

    public function visiteProtocoleByDate(?DateTime $debut, ?DateTime $fin)
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
            ->andWhere("v.statut ='" . Visite::FAITE . "' ")
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
            ->orderBy('v.date, e.nom')
            ->groupBy('month')
            ->addGroupBy('year')
            ->addGroupBy('i.essai')
            ->getQuery()
            ->getResult();

        return $this->orderByDate($visitesByMonth, $debut, $fin);
    }

// Ong3-CANVAS 9-visite/arc/date----------------------------------------------------------------------------

    public function visiteArcByDate(?DateTime $debut, ?DateTime $fin)
    {
        if (!$debut && !$fin) {
            return false;
        }

        $em = $this->getDoctrine()->getManager();

        $visitesByMonth = $em->getRepository(Visite::class)
            ->createQueryBuilder('v')
            ->select(' count(v) as nb, MONTH(v.date) AS month, YEAR(v.date) AS year, a.nomArc as nom, a.prenomArc as prenom')
            ->leftJoin('v.arc', 'a')
            ->where('v.date IS NOT NULL')
            ->andWhere('v.date >= :debut')
            ->andWhere('v.date <= :fin')
            ->andWhere("v.statut ='" . Visite::FAITE . "' ")
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
            ->orderBy('v.date, a.nomArc, a.prenomArc')
            ->groupBy('month')
            ->addGroupBy('year')
            ->addGroupBy('v.arc')
            ->getQuery()
            ->getResult();

        return $this->orderByDate($visitesByMonth, $debut, $fin);
    }

// Ong5-CANVAS 5-Inclusion par service---------------------------------------------------------------------------

    public function inclusionsByService(?DateTime $debut, ?DateTime $fin)
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
            ->orderBy('nb', 'DESC')
            ->getQuery()
            ->getResult();

        return $inclusionsByMounth;
    }

// Ong1-CANVAS 7-Inclusions cumulées par protocole ( pie)---------------------------------------------------------------------------

    public function inclusionByArc(?DateTime $debut = null, ?DateTime $fin = null)
    {
        $em = $this->getDoctrine()->getManager();

        if (!$debut && !$fin) {
            $inclusions = $em->getRepository(Inclusion::class)
                ->createQueryBuilder('i')
                ->select('i')
                ->join('i.arc', 'a')
                ->addSelect("a")
                ->orderBy('a.nomArc, a.prenomArc')
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
                ->join('i.arc', 'a')
                ->addSelect("a")
                ->orderBy('a.nomArc, a.prenomArc')
                ->getQuery()
                ->getResult();
        }

        $inclusionArc = [];
        foreach ($inclusions as $inclusion) {
            $arc = $inclusion->getArc();
            if (!$arc) {
                continue;
            }
            if (!isset($inclusionArc[$arc->getNomPrenom()])) {
                $inclusionArc[$arc->getNomPrenom()] = 0;
            }
            $inclusionArc[$arc->getNomPrenom()] += 1;
        }

        ksort($inclusionArc);
        return $inclusionArc;
    }

    public function inclusionsByProtocole(?DateTime $debut, ?DateTime $fin)
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
            ->orderBy('nb', 'DESC')
            ->getQuery()
            ->getResult();

        return $inclusionsByProtocole;
    }

    private function inclusionByTypeEssai(DateTime $debut, DateTime $fin)
    {
        if (!$debut && !$fin) {
            return false;
        }

        $em = $this->getDoctrine()->getManager();
        $inclusionByTypeEssai = $em->getRepository(Inclusion::class)
            ->createQueryBuilder('i')
            ->select(' count(i) as nb, e.typeEssai as type')
            ->join('i.essai', 'e')
            ->where('i.datInc IS NOT NULL')
            ->andWhere('i.datInc >= :debut')
            ->andWhere('i.datInc <= :fin')
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
            ->orderBy('type')
            ->groupBy('type')
            ->orderBy('nb', 'DESC')
            ->getQuery()
            ->getResult();

        return $inclusionByTypeEssai;
    }

    private function inclusionByTypeProm(DateTime $debut, DateTime $fin)
    {
        if (!$debut && !$fin) {
            return false;
        }

        $em = $this->getDoctrine()->getManager();
        $inclusionByTypeProm = $em->getRepository(Inclusion::class)
            ->createQueryBuilder('i')
            ->select(' count(i) as nb, e.typeProm as type')
            ->join('i.essai', 'e')
            ->where('i.datInc IS NOT NULL')
            ->andWhere('i.datInc >= :debut')
            ->andWhere('i.datInc <= :fin')
            ->setParameter('debut', $debut)
            ->setParameter('fin', $fin)
            ->orderBy('type')
            ->groupBy('type')
            ->orderBy('nb', 'DESC')
            ->getQuery()
            ->getResult();

        return $inclusionByTypeProm;
    }

    /**
     * @Route("/analyse/tableau", name="analyseTableau")
     * @return Response
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

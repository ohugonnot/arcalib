<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Visite;
use AppBundle\Factory\VisiteFactory;
use AppBundle\Services\CsvToArray;
use DateTime;
use Exception;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @Route("/arcalib")
 */
class VisiteController extends Controller
{

//-----------------------------------DELETE VISITE  ->  deleteVisite-------------------------
    /**
     * @Route("/visite/supprimer/{id}", name="deleteVisite", options={"expose"=true})
     * @param Visite $visite
     * @return JsonResponse
     */
    public function deleteVisiteAction(Visite $visite)
    {
        $em = $this->getDoctrine()->getManager();

        $em->remove($visite);
        $em->flush();

        return new JsonResponse(true);
    }

//-----------------------------------LISTE VISITE  ->  listeVisites-------------------------

    /**
     * Todo : duplicate code content search
     * @Route("/visites/", name="listeVisites")
     * @param Request $request
     * @return Response
     */
    public function listeVisitesAction(Request $request)
    {
        $search = $request->query->get("recherche");
        if ($search == null) {
            $search = '%%';
            $searchId = '%%';
        } else {
            $searchId = '%%';
            if (preg_match("#id=#Ui", $search)) {
                $searchId = explode("id=", $search);
                $searchId = $searchId[1];
            }
        }

        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        $emVisite = $em->getRepository(Visite::class);
        $query = $emVisite->getQuery($user, $search, $searchId);

        $paginator = $this->get('knp_paginator');
        $visites = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            20/*limit per page*/,
            array('defaultSortFieldName' => ['v.id'], 'defaultSortDirection' => 'asc')
        );

        return $this->render('visite/listeVisites.html.twig', [
            'visites' => $visites
        ]);
    }

    /**
     * @Route("/visite/edit/{id}", name="editVisite", options={"expose"=true})
     * @Route("/visite/save/{id}", name="saveVisite", options={"expose"=true})
     * @param Request $request
     * @param null $id
     * @param VisiteFactory $visiteFactory
     * @return JsonResponse
     */
    public function saveVisitetAction(Request $request, VisiteFactory $visiteFactory, $id = null)
    {
        $em = $this->getDoctrine()->getManager();
        $visite = $em->getRepository(Visite::class)->find($id);
        if (!$visite) {
            $visite = new Visite();
            $new = true;
        }

        $visite = $visiteFactory->hydrate($visite, $request->request->get("appbundle_visite"));

        if (isset($visite->errorsMessage) && $visite->errorsMessage)
            return new JsonResponse(["success" => false, "message" => $visite->errorsMessage]);

        if (isset($new) && $new)
            $em->persist($visite);
        $em->flush();

        return new JsonResponse(["success" => true, "visite" => ["id" => $visite->getId()]]);
    }

    /**
     * @Route("/visite/advanced/recherche/{day}/{month}/{year}", name="searchVisitesByDate", options={"expose"=true})
     * @Route("/visite/advanced/recherche/{query}", name="searchVisites", options={"expose"=true})
     * @param Request $request
     * @param null $day
     * @param null $month
     * @param null $year
     * @param null $query
     * @return JsonResponse
     * @throws Exception
     */
    public function searchVisitesAction(Request $request, $day = null, $month = null, $year = null, $query = null)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $date = new DateTime();
        $date->setDate($year, $month, $day);
        $filters = $request->request->get("filters");
        $emVisite = $em->getRepository(Visite::class);
        $visites = $emVisite->findAdvancedArray($date, $filters, $user);

        return new JsonResponse($visites);
    }

    /**
     * @Route("/visite/export", name="exportVisites", options={"expose"=true})
     * @Security("has_role('ROLE_ARC')")
     * @param CsvToArray $export
     * @return StreamedResponse
     */
    public function exportEssaisAction(CsvToArray $export)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $emVisite = $em->getRepository(Visite::class);
        $visites = $emVisite->findAllByUser($user);

        return $export->exportCSV($visites, "visites");
    }

    /**
     * @Route("/visite/updateDuree", name="updateDuree", options={"expose"=true})
     * @Security("has_role('ROLE_ADMIN')")
     */
    public function updateDuree(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $emVisite = $em->getRepository(Visite::class);
        $visites = $emVisite->findAll();

        # Tableau visite hervé
        $tempsVisites = [
            "APICAT" => [
                VISITE::SCREEN => 15,
                VISITE::INCLUSION => 120,
                VISITE::SUIVI => 45,
                VISITE::FIN_ETUDE => 45,
                VISITE::MONITORAGE => 60,
                VISITE::UNIQUE => 0,
            ],
            "FORSYA 1" => [
                VISITE::SCREEN => 15,
                VISITE::INCLUSION => 90,
                VISITE::SUIVI => 0,
                VISITE::FIN_ETUDE => 0,
                VISITE::MONITORAGE => 30,
                VISITE::UNIQUE => 0,
            ],
            "FORSYA 2" => [
                VISITE::SCREEN => 15,
                VISITE::INCLUSION => 90,
                VISITE::SUIVI => 0,
                VISITE::FIN_ETUDE => 0,
                VISITE::MONITORAGE => 30,
                VISITE::UNIQUE => 0,
            ],
            "GREAT" => [
                VISITE::SCREEN => 10,
                VISITE::INCLUSION => 60,
                VISITE::SUIVI => 20,
                VISITE::FIN_ETUDE => 20,
                VISITE::MONITORAGE => 20,
                VISITE::UNIQUE => 0,
            ],
            "MAPIE" => [
                VISITE::SCREEN => 20,
                VISITE::INCLUSION => 90,
                VISITE::SUIVI => 30,
                VISITE::FIN_ETUDE => 0,
                VISITE::MONITORAGE => 0,
                VISITE::UNIQUE => 0,
            ],
            "MYPEBS" => [
                VISITE::SCREEN => 90,
                VISITE::INCLUSION => 30,
                VISITE::SUIVI => 0,
                VISITE::FIN_ETUDE => 0,
                VISITE::MONITORAGE => 0,
                VISITE::UNIQUE => 0,
            ],
            "PRO SPIRIT" => [
                VISITE::SCREEN => 20,
                VISITE::INCLUSION => 45,
                VISITE::SUIVI => 30,
                VISITE::FIN_ETUDE => 30,
                VISITE::MONITORAGE => 30,
                VISITE::UNIQUE => 0,
            ],
            "SMART2T" => [
                VISITE::SCREEN => 5,
                VISITE::INCLUSION => 45,
                VISITE::SUIVI => 30,
                VISITE::FIN_ETUDE => 30,
                VISITE::MONITORAGE => 30,
                VISITE::UNIQUE => 0,
            ],
            "SUNSTAR" => [
                VISITE::SCREEN => 30,
                VISITE::INCLUSION => 90,
                VISITE::SUIVI => 45,
                VISITE::FIN_ETUDE => 45,
                VISITE::MONITORAGE => 60,
                VISITE::UNIQUE => 0,
            ],
            "TOFAST" => [
                VISITE::SCREEN => 15,
                VISITE::INCLUSION => 45,
                VISITE::SUIVI => 30,
                VISITE::FIN_ETUDE => 30,
                VISITE::MONITORAGE => 30,
                VISITE::UNIQUE => 0,
            ],
            "UPSTAND" => [
                VISITE::SCREEN => 5,
                VISITE::INCLUSION => 60,
                VISITE::SUIVI => 30,
                VISITE::FIN_ETUDE => 30,
                VISITE::MONITORAGE => 60,
                VISITE::UNIQUE => 0,
            ],
            "OPALE" => [
                VISITE::SCREEN => 5,
                VISITE::INCLUSION => 60,
                VISITE::SUIVI => 30,
                VISITE::FIN_ETUDE => 30,
                VISITE::MONITORAGE => 60,
                VISITE::UNIQUE => 0,
            ],
            "PERFUSE" => [
                VISITE::SCREEN => 5,
                VISITE::INCLUSION => 60,
                VISITE::SUIVI => 30,
                VISITE::FIN_ETUDE => 30,
                VISITE::MONITORAGE => 60,
                VISITE::UNIQUE => 0,
            ],
            "RABE REAL" => [
                VISITE::SCREEN => 15,
                VISITE::INCLUSION => 45,
                VISITE::SUIVI => 20,
                VISITE::FIN_ETUDE => 20,
                VISITE::MONITORAGE => 60,
                VISITE::UNIQUE => 0,
            ],
            "UPHOLD" => [
                VISITE::SCREEN => 20,
                VISITE::INCLUSION => 90,
                VISITE::SUIVI => 60,
                VISITE::FIN_ETUDE => 60,
                VISITE::MONITORAGE => 60,
                VISITE::UNIQUE => 0,
            ],
            "XARENO" => [
                VISITE::SCREEN => 30,
                VISITE::INCLUSION => 60,
                VISITE::SUIVI => 30,
                VISITE::FIN_ETUDE => 30,
                VISITE::MONITORAGE => 60,
                VISITE::UNIQUE => 0,
            ],
            "CRI-RA" => [
                VISITE::SCREEN => 0,
                VISITE::INCLUSION => 90,
                VISITE::SUIVI => 60,
                VISITE::FIN_ETUDE => 60,
                VISITE::MONITORAGE => 60,
                VISITE::UNIQUE => 0,
            ],
            "P59-DURIGAST" => [
                VISITE::SCREEN => 45,
                VISITE::INCLUSION => 90,
                VISITE::SUIVI => 30,
                VISITE::FIN_ETUDE => 30,
                VISITE::MONITORAGE => 30,
                VISITE::UNIQUE => 0,
            ],
            "PREVOX" => [
                VISITE::SCREEN => 0,
                VISITE::INCLUSION => 45,
                VISITE::SUIVI => 0,
                VISITE::FIN_ETUDE => 0,
                VISITE::MONITORAGE => 0,
                VISITE::UNIQUE => 0,
            ],
            "PERSEPOLIS" => [
                VISITE::SCREEN => 0,
                VISITE::INCLUSION => 45,
                VISITE::SUIVI => 0,
                VISITE::FIN_ETUDE => 0,
                VISITE::MONITORAGE => 0,
                VISITE::UNIQUE => 0,
            ],
            "ELEGANT" => [
                VISITE::SCREEN => 10,
                VISITE::INCLUSION => 45,
                VISITE::SUIVI => 20,
                VISITE::FIN_ETUDE => 20,
                VISITE::MONITORAGE => 0,
                VISITE::UNIQUE => 0,
            ],
            "PARACT" => [
                VISITE::SCREEN => 10,
                VISITE::INCLUSION => 45,
                VISITE::SUIVI => 30,
                VISITE::FIN_ETUDE => 30,
                VISITE::MONITORAGE => 0,
                VISITE::UNIQUE => 0,
            ],
            "ASCALATE" => [
                VISITE::SCREEN => 0,
                VISITE::INCLUSION => 0,
                VISITE::SUIVI => 0,
                VISITE::FIN_ETUDE => 0,
                VISITE::MONITORAGE => 0,
                VISITE::UNIQUE => 0,
            ],
            "COLON-IM" => [
                VISITE::SCREEN => 10,
                VISITE::INCLUSION => 45,
                VISITE::SUIVI => 0,
                VISITE::FIN_ETUDE => 0,
                VISITE::MONITORAGE => 0,
                VISITE::UNIQUE => 0,
            ],
            "COOK" => [
                VISITE::SCREEN => 0,
                VISITE::INCLUSION => 0,
                VISITE::SUIVI => 0,
                VISITE::FIN_ETUDE => 0,
                VISITE::MONITORAGE => 0,
                VISITE::UNIQUE => 0,
            ],
            "PROMETCO" => [
                VISITE::SCREEN => 30,
                VISITE::INCLUSION => 40,
                VISITE::SUIVI => 40,
                VISITE::FIN_ETUDE => 40,
                VISITE::MONITORAGE => 0,
                VISITE::UNIQUE => 0,
            ],
            "EQUATEUR" => [
                VISITE::SCREEN => 10,
                VISITE::INCLUSION => 45,
                VISITE::SUIVI => 30,
                VISITE::FIN_ETUDE => 30,
                VISITE::MONITORAGE => 0,
                VISITE::UNIQUE => 0,
            ],
            "ERAV" => [
                VISITE::SCREEN => 10,
                VISITE::INCLUSION => 20,
                VISITE::SUIVI => 20,
                VISITE::FIN_ETUDE => 20,
                VISITE::MONITORAGE => 0,
                VISITE::UNIQUE => 0,
            ],
            "FRENCH rmd covid" => [
                VISITE::SCREEN => 0,
                VISITE::INCLUSION => 0,
                VISITE::SUIVI => 0,
                VISITE::FIN_ETUDE => 0,
                VISITE::MONITORAGE => 0,
                VISITE::UNIQUE => 20,
            ],
            "LIBELULE" => [
                VISITE::SCREEN => 20,
                VISITE::INCLUSION => 60,
                VISITE::SUIVI => 60,
                VISITE::FIN_ETUDE => 60,
                VISITE::MONITORAGE => 0,
                VISITE::UNIQUE => 0,
            ],
            "MENTCOVRMD" => [
                VISITE::SCREEN => 0,
                VISITE::INCLUSION => 0,
                VISITE::SUIVI => 0,
                VISITE::FIN_ETUDE => 0,
                VISITE::MONITORAGE => 0,
                VISITE::UNIQUE => 20,
            ],
            "PROMPT" => [
                VISITE::SCREEN => 10,
                VISITE::INCLUSION => 60,
                VISITE::SUIVI => 20,
                VISITE::FIN_ETUDE => 20,
                VISITE::MONITORAGE => 0,
                VISITE::UNIQUE => 0,
            ],
            "P32-ESOSTRATE" => [
                VISITE::SCREEN => 0,
                VISITE::INCLUSION => 0,
                VISITE::SUIVI => 0,
                VISITE::FIN_ETUDE => 0,
                VISITE::MONITORAGE => 0,
                VISITE::UNIQUE => 0,
            ],
            "P34-ADAGE" => [
                VISITE::SCREEN => 30,
                VISITE::INCLUSION => 45,
                VISITE::SUIVI => 30,
                VISITE::FIN_ETUDE => 30,
                VISITE::MONITORAGE => 0,
                VISITE::UNIQUE => 0,
            ],
            "P50-ASPIK" => [
                VISITE::SCREEN => 20,
                VISITE::INCLUSION => 45,
                VISITE::SUIVI => 30,
                VISITE::FIN_ETUDE => 20,
                VISITE::MONITORAGE => 0,
                VISITE::UNIQUE => 0,
            ],
            "P51-GASFOX" => [
                VISITE::SCREEN => 0,
                VISITE::INCLUSION => 0,
                VISITE::SUIVI => 0,
                VISITE::FIN_ETUDE => 0,
                VISITE::MONITORAGE => 0,
                VISITE::UNIQUE => 0,
            ],
            "P52-IROCAS" => [
                VISITE::SCREEN => 20,
                VISITE::INCLUSION => 60,
                VISITE::SUIVI => 30,
                VISITE::FIN_ETUDE => 30,
                VISITE::MONITORAGE => 60,
                VISITE::UNIQUE => 0,
            ],
            "P70-CIRCULATE" => [
                VISITE::SCREEN => 10,
                VISITE::INCLUSION => 45,
                VISITE::SUIVI => 20,
                VISITE::FIN_ETUDE => 20,
                VISITE::MONITORAGE => 0,
                VISITE::UNIQUE => 0,
            ],
            "REOFLAC" => [
                VISITE::SCREEN => 10,
                VISITE::INCLUSION => 90,
                VISITE::SUIVI => 90,
                VISITE::FIN_ETUDE => 90,
                VISITE::MONITORAGE => 0,
                VISITE::UNIQUE => 0,
            ],
            "TOPSATI" => [
                VISITE::SCREEN => 10,
                VISITE::INCLUSION => 60,
                VISITE::SUIVI => 60,
                VISITE::FIN_ETUDE => 60,
                VISITE::MONITORAGE => 60,
                VISITE::UNIQUE => 0,
            ],
            "BENCHMARK" => [
                VISITE::SCREEN => 5,
                VISITE::INCLUSION => 120,
                VISITE::SUIVI => 45,
                VISITE::FIN_ETUDE => 0,
                VISITE::MONITORAGE => 15,
                VISITE::UNIQUE => 0,
            ],
            "MITRAGISTER" => [
                VISITE::SCREEN => 5,
                VISITE::INCLUSION => 60,
                VISITE::SUIVI => 15,
                VISITE::FIN_ETUDE => 0,
                VISITE::MONITORAGE => 0,
                VISITE::UNIQUE => 0,
            ],
            "France TAVI" => [
                VISITE::SCREEN => 5,
                VISITE::INCLUSION => 45,
                VISITE::SUIVI => 0,
                VISITE::FIN_ETUDE => 0,
                VISITE::MONITORAGE => 0,
                VISITE::UNIQUE => 0,
            ],
        ];

        $updated_visites = [];
        $protocole_matched = [];
        foreach ($visites as $visite) {
            $date_visite = $visite->getDate();
            $date_min = (new DateTime("01/01/2019"))->setTime(0, 0, 0);
            if ($date_visite < $date_min)
                continue;

            $inclusion = $visite->getInclusion();
            if (!$inclusion)
                continue;

            $essai = $inclusion->getEssai();
            if (!$essai)
                continue;
            $essai_name = trim(strtolower($essai->getNom()));
            foreach ($tempsVisites as $essai => $tempsVisite) {
                $essai = trim(strtolower($essai));
                if ($essai == $essai_name) {
                    $protocole_matched[$essai] = isset($protocole_matched[$essai]["nb_visite"]) ? $protocole_matched[$essai]["nb_visite"] : 0;
                    $protocole_matched[$essai] = isset($protocole_matched[$essai]["temps_total"]) ? $protocole_matched[$essai]["temps_total"] : 0;
                    foreach ($tempsVisite as $type => $temps) {
                        if ($visite->getType() == $type && $visite->getStatut() != VISITE::NON_FAITE) {
                            $visite->setDuree($temps);
                            $updated_visites[] = $visite;
                            $protocole_matched[$essai]["nb_visite"]++;
                            $protocole_matched[$essai]["temps_total"] += $visite->getDuree();
                        }
                    }
                }
            }
        }
        $total = 0;
        foreach ($updated_visites as $updated_visite) {
            $total += $updated_visite->getDuree();
        }
        dump($updated_visites);
        dump($total);
        dump($protocole_matched);
        return new Response();
    }
}

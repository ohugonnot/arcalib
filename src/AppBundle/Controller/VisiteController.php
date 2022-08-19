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
                VISITE::SCREEN => 30,
                VISITE::INCLUSION => 150,
                VISITE::SUIVI => 60,
                VISITE::FIN_ETUDE => 45,
                VISITE::MONITORAGE => 60,
                VISITE::UNIQUE => 0,
            ],
            "Ascalate - CAIN457HDE01" => [
                VISITE::SCREEN => 180,
                VISITE::INCLUSION => 180,
                VISITE::SUIVI => 180,
                VISITE::FIN_ETUDE => 180,
                VISITE::MONITORAGE => 180,
                VISITE::UNIQUE => 0,
            ],
            "BBCOVID" => [
                VISITE::SCREEN => 120,
                VISITE::INCLUSION => 180,
                VISITE::SUIVI => 120,
                VISITE::FIN_ETUDE => 60,
                VISITE::MONITORAGE => 60,
                VISITE::UNIQUE => 60,
            ],
            "BENCHMARK Registry" => [
                VISITE::SCREEN => 30,
                VISITE::INCLUSION => 120,
                VISITE::SUIVI => 60,
                VISITE::FIN_ETUDE => 60,
                VISITE::MONITORAGE => 15,
                VISITE::UNIQUE => 60,
            ],
            "COLOMIN2" => [
                VISITE::SCREEN => 30,
                VISITE::INCLUSION => 30,
                VISITE::SUIVI => 30,
                VISITE::FIN_ETUDE => 30,
                VISITE::MONITORAGE => 0,
                VISITE::UNIQUE => 0,
            ],
            "COLON-IM" => [
                VISITE::SCREEN => 30,
                VISITE::INCLUSION => 90,
                VISITE::SUIVI => 45,
                VISITE::FIN_ETUDE => 30,
                VISITE::MONITORAGE => 0,
                VISITE::UNIQUE => 30,
            ],
            "COOK" => [
                VISITE::SCREEN => 60,
                VISITE::INCLUSION => 180,
                VISITE::SUIVI => 180,
                VISITE::FIN_ETUDE => 120,
                VISITE::MONITORAGE => 180,
                VISITE::UNIQUE => 0,
            ],
            "COVAX" => [
                VISITE::SCREEN => 40,
                VISITE::INCLUSION => 30,
                VISITE::SUIVI => 30,
                VISITE::FIN_ETUDE => 30,
                VISITE::MONITORAGE => 0,
                VISITE::UNIQUE => 40,
            ],
            "CRI-RA" => [
                VISITE::SCREEN => 15,
                VISITE::INCLUSION => 120,
                VISITE::SUIVI => 90,
                VISITE::FIN_ETUDE => 60,
                VISITE::MONITORAGE => 60,
                VISITE::UNIQUE => 0,
            ],
            "DeFacTo" => [
                VISITE::SCREEN => 30,
                VISITE::INCLUSION => 90,
                VISITE::SUIVI => 60,
                VISITE::FIN_ETUDE => 60,
                VISITE::MONITORAGE => 60,
                VISITE::UNIQUE => 60,
            ],
            "Diamond Temp" => [
                VISITE::SCREEN => 30,
                VISITE::INCLUSION => 90,
                VISITE::SUIVI => 60,
                VISITE::FIN_ETUDE => 60,
                VISITE::MONITORAGE => 0,
                VISITE::UNIQUE => 0,
            ],
            "ELEGANT" => [
                VISITE::SCREEN => 30,
                VISITE::INCLUSION => 60,
                VISITE::SUIVI => 45,
                VISITE::FIN_ETUDE => 45,
                VISITE::MONITORAGE => 0,
                VISITE::UNIQUE => 30,
            ],
            "EQUATEUR" => [
                VISITE::SCREEN => 20,
                VISITE::INCLUSION => 90,
                VISITE::SUIVI => 60,
                VISITE::FIN_ETUDE => 60,
                VISITE::MONITORAGE => 60,
                VISITE::UNIQUE => 0,
            ],
            "ERAV" => [
                VISITE::SCREEN => 20,
                VISITE::INCLUSION => 30,
                VISITE::SUIVI => 30,
                VISITE::FIN_ETUDE => 30,
                VISITE::MONITORAGE => 0,
                VISITE::UNIQUE => 0,
            ],
            "EVIDENS" => [
                VISITE::SCREEN => 30,
                VISITE::INCLUSION => 90,
                VISITE::SUIVI => 90,
                VISITE::FIN_ETUDE => 90,
                VISITE::MONITORAGE => 60,
                VISITE::UNIQUE => 0,
            ],
            "FLARAMIS" => [
                VISITE::SCREEN => 30,
                VISITE::INCLUSION => 120,
                VISITE::SUIVI => 90,
                VISITE::FIN_ETUDE => 90,
                VISITE::MONITORAGE => 60,
                VISITE::UNIQUE => 60,
            ],
            "FORSYA" => [
                VISITE::SCREEN => 15,
                VISITE::INCLUSION => 90,
                VISITE::SUIVI => 0,
                VISITE::FIN_ETUDE => 0,
                VISITE::MONITORAGE => 30,
                VISITE::UNIQUE => 0,
                VISITE::AUTRE => 30,
            ],
            "France TAVI" => [
                VISITE::SCREEN => 30,
                VISITE::INCLUSION => 45,
                VISITE::SUIVI => 60,
                VISITE::FIN_ETUDE => 60,
                VISITE::MONITORAGE => 0,
                VISITE::UNIQUE => 0,
            ],
            "FRENCH rmd covid" => [
                VISITE::SCREEN => 30,
                VISITE::INCLUSION => 60,
                VISITE::SUIVI => 60,
                VISITE::FIN_ETUDE => 60,
                VISITE::MONITORAGE => 0,
                VISITE::UNIQUE => 20,
            ],
            "FX SOLUTIONS" => [
                VISITE::SCREEN => 20,
                VISITE::INCLUSION => 30,
                VISITE::SUIVI => 60,
                VISITE::FIN_ETUDE => 60,
                VISITE::MONITORAGE => 0,
                VISITE::UNIQUE => 0,
            ],
            "GO BEYOND" => [
                VISITE::SCREEN => 40,
                VISITE::INCLUSION => 120,
                VISITE::SUIVI => 120,
                VISITE::FIN_ETUDE => 120,
                VISITE::MONITORAGE => 0,
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
            "HOPICOV" => [
                VISITE::SCREEN => 0,
                VISITE::INCLUSION => 0,
                VISITE::SUIVI => 0,
                VISITE::FIN_ETUDE => 0,
                VISITE::MONITORAGE => 0,
                VISITE::UNIQUE => 120,
            ],
            "I4V-MC-JAJA" => [
                VISITE::SCREEN => 120,
                VISITE::INCLUSION => 180,
                VISITE::SUIVI => 180,
                VISITE::FIN_ETUDE => 180,
                VISITE::MONITORAGE => 120,
                VISITE::UNIQUE => 0,
            ],
            "LIBELULE 01" => [
                VISITE::SCREEN => 30,
                VISITE::INCLUSION => 150,
                VISITE::SUIVI => 90,
                VISITE::FIN_ETUDE => 60,
                VISITE::MONITORAGE => 0,
                VISITE::UNIQUE => 0,
            ],
            "MAJIK" => [
                VISITE::SCREEN => 0,
                VISITE::INCLUSION => 30,
                VISITE::SUIVI => 30,
                VISITE::FIN_ETUDE => 30,
                VISITE::MONITORAGE => 0,
                VISITE::UNIQUE => 30,
            ],
            "MAPIE" => [
                VISITE::SCREEN => 20,
                VISITE::INCLUSION => 90,
                VISITE::SUIVI => 60,
                VISITE::FIN_ETUDE => 30,
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
            "MITRAGISTER" => [
                VISITE::SCREEN => 5,
                VISITE::INCLUSION => 60,
                VISITE::SUIVI => 45,
                VISITE::FIN_ETUDE => 0,
                VISITE::MONITORAGE => 0,
                VISITE::UNIQUE => 0,
            ],
            "MYPEBS" => [
                VISITE::SCREEN => 30,
                VISITE::INCLUSION => 90,
                VISITE::SUIVI => 30,
                VISITE::FIN_ETUDE => 30,
                VISITE::MONITORAGE => 0,
                VISITE::UNIQUE => 0,
            ],
            "OPALE" => [
                VISITE::SCREEN => 5,
                VISITE::INCLUSION => 60,
                VISITE::SUIVI => 45,
                VISITE::FIN_ETUDE => 45,
                VISITE::MONITORAGE => 60,
                VISITE::UNIQUE => 0,
            ],
            "OXYCOVID" => [
                VISITE::SCREEN => 30,
                VISITE::INCLUSION => 60,
                VISITE::SUIVI => 60,
                VISITE::FIN_ETUDE => 60,
                VISITE::MONITORAGE => 0,
                VISITE::UNIQUE => 0,
            ],
            "PANDAURA" => [
                VISITE::SCREEN => 0,
                VISITE::INCLUSION => 0,
                VISITE::SUIVI => 0,
                VISITE::FIN_ETUDE => 0,
                VISITE::MONITORAGE => 0,
                VISITE::UNIQUE => 45,
            ],
            "PARACT" => [
                VISITE::SCREEN => 30,
                VISITE::INCLUSION => 45,
                VISITE::SUIVI => 45,
                VISITE::FIN_ETUDE => 45,
                VISITE::MONITORAGE => 0,
                VISITE::UNIQUE => 0,
            ],
            "PARROTFISH" => [
                VISITE::SCREEN => 30,
                VISITE::INCLUSION => 120,
                VISITE::SUIVI => 120,
                VISITE::FIN_ETUDE => 90,
                VISITE::MONITORAGE => 0,
                VISITE::UNIQUE => 0,
            ],
            "PERFUSE" => [
                VISITE::SCREEN => 25,
                VISITE::INCLUSION => 120,
                VISITE::SUIVI => 90,
                VISITE::FIN_ETUDE => 90,
                VISITE::MONITORAGE => 0,
                VISITE::UNIQUE => 0,
            ],
            "PERSEPOLIS" => [
                VISITE::SCREEN => 0,
                VISITE::INCLUSION => 50,
                VISITE::SUIVI => 0,
                VISITE::FIN_ETUDE => 0,
                VISITE::MONITORAGE => 0,
                VISITE::UNIQUE => 50,
            ],
            "PREPARE" => [
                VISITE::SCREEN => 45,
                VISITE::INCLUSION => 90,
                VISITE::SUIVI => 90,
                VISITE::FIN_ETUDE => 90,
                VISITE::MONITORAGE => 90,
                VISITE::UNIQUE => 0,
            ],
            "PREVOX" => [
                VISITE::SCREEN => 0,
                VISITE::INCLUSION => 45,
                VISITE::SUIVI => 0,
                VISITE::FIN_ETUDE => 5,
                VISITE::MONITORAGE => 0,
                VISITE::UNIQUE => 45,
            ],
            "PRO SPIRIT" => [
                VISITE::SCREEN => 20,
                VISITE::INCLUSION => 50,
                VISITE::SUIVI => 45,
                VISITE::FIN_ETUDE => 45,
                VISITE::MONITORAGE => 30,
                VISITE::UNIQUE => 0,
            ],
            "PRODIGE 1703 POCHI" => [
                VISITE::SCREEN => 90,
                VISITE::INCLUSION => 120,
                VISITE::SUIVI => 120,
                VISITE::FIN_ETUDE => 60,
                VISITE::MONITORAGE => 0,
                VISITE::UNIQUE => 0,
            ],
            "Prodige 32 - Esostrate" => [
                VISITE::SCREEN => 120,
                VISITE::INCLUSION => 120,
                VISITE::SUIVI => 150,
                VISITE::FIN_ETUDE => 150,
                VISITE::MONITORAGE => 0,
                VISITE::UNIQUE => 0,
            ],
            "Prodige 34 - ADAGE" => [
                VISITE::SCREEN => 45,
                VISITE::INCLUSION => 180,
                VISITE::SUIVI => 90,
                VISITE::FIN_ETUDE => 90,
                VISITE::MONITORAGE => 60,
                VISITE::UNIQUE => 0,
            ],
            "Prodige 44 - PANDAS" => [
                VISITE::SCREEN => 120,
                VISITE::INCLUSION => 180,
                VISITE::SUIVI => 180,
                VISITE::FIN_ETUDE => 180,
                VISITE::MONITORAGE => 0,
                VISITE::UNIQUE => 0,
            ],
            "Prodige 49 - OSCAR" => [
                VISITE::SCREEN => 120,
                VISITE::INCLUSION => 180,
                VISITE::SUIVI => 180,
                VISITE::FIN_ETUDE => 180,
                VISITE::MONITORAGE => 0,
                VISITE::UNIQUE => 0,
            ],
            "Prodige 50 - ASPIK" => [
                VISITE::SCREEN => 60,
                VISITE::INCLUSION => 120,
                VISITE::SUIVI => 120,
                VISITE::FIN_ETUDE => 120,
                VISITE::MONITORAGE => 0,
                VISITE::UNIQUE => 0,
            ],
            "Prodige 51 - GASTFOX" => [
                VISITE::SCREEN => 60,
                VISITE::INCLUSION => 180,
                VISITE::SUIVI => 150,
                VISITE::FIN_ETUDE => 120,
                VISITE::MONITORAGE => 120,
                VISITE::UNIQUE => 0,
            ],
            "Prodige 52 - IROCAS" => [
                VISITE::SCREEN => 60,
                VISITE::INCLUSION => 150,
                VISITE::SUIVI => 150,
                VISITE::FIN_ETUDE => 140,
                VISITE::MONITORAGE => 0,
                VISITE::UNIQUE => 0,
            ],
            "Prodige 54 - SAMCO" => [
                VISITE::SCREEN => 60,
                VISITE::INCLUSION => 120,
                VISITE::SUIVI => 150,
                VISITE::FIN_ETUDE => 150,
                VISITE::MONITORAGE => 120,
                VISITE::UNIQUE => 0,
            ],
            "PRODIGE 59-DURIGAST" => [
                VISITE::SCREEN => 60,
                VISITE::INCLUSION => 180,
                VISITE::SUIVI => 150,
                VISITE::FIN_ETUDE => 150,
                VISITE::MONITORAGE => 120,
                VISITE::UNIQUE => 0,
            ],
            "Prodige 70 - CIRCULATE" => [
                VISITE::SCREEN => 60,
                VISITE::INCLUSION => 120,
                VISITE::SUIVI => 60,
                VISITE::FIN_ETUDE => 60,
                VISITE::MONITORAGE => 0,
                VISITE::UNIQUE => 0,
            ],
            "PRODUCT HF" => [
                VISITE::SCREEN => 0,
                VISITE::INCLUSION => 60,
                VISITE::SUIVI => 60,
                VISITE::FIN_ETUDE => 60,
                VISITE::MONITORAGE => 0,
                VISITE::UNIQUE => 0,
            ],
            "PROMETCO" => [
                VISITE::SCREEN => 30,
                VISITE::INCLUSION => 40,
                VISITE::SUIVI => 45,
                VISITE::FIN_ETUDE => 40,
                VISITE::MONITORAGE => 0,
                VISITE::UNIQUE => 0,
            ],
            "PsABIOnd" => [
                VISITE::SCREEN => 30,
                VISITE::INCLUSION => 75,
                VISITE::SUIVI => 45,
                VISITE::FIN_ETUDE => 60,
                VISITE::MONITORAGE => 0,
                VISITE::UNIQUE => 0,
            ],
            "PROMPT" => [
                VISITE::SCREEN => 10,
                VISITE::INCLUSION => 60,
                VISITE::SUIVI => 40,
                VISITE::FIN_ETUDE => 40,
                VISITE::MONITORAGE => 0,
                VISITE::UNIQUE => 0,
            ],
            "PROUST" => [
                VISITE::SCREEN => 45,
                VISITE::INCLUSION => 60,
                VISITE::SUIVI => 60,
                VISITE::FIN_ETUDE => 90,
                VISITE::MONITORAGE => 0,
                VISITE::UNIQUE => 0,
            ],
            "RABE REAL - IV MC B009" => [
                VISITE::SCREEN => 15,
                VISITE::INCLUSION => 60,
                VISITE::SUIVI => 45,
                VISITE::FIN_ETUDE => 45,
                VISITE::MONITORAGE => 60,
                VISITE::UNIQUE => 0,
            ],
            "REGISTRE ART" => [
                VISITE::SCREEN => 0,
                VISITE::INCLUSION => 30,
                VISITE::SUIVI => 0,
                VISITE::FIN_ETUDE => 0,
                VISITE::MONITORAGE => 30,
                VISITE::UNIQUE => 30,
            ],
            "REOFLAC" => [
                VISITE::SCREEN => 10,
                VISITE::INCLUSION => 90,
                VISITE::SUIVI => 90,
                VISITE::FIN_ETUDE => 90,
                VISITE::MONITORAGE => 0,
                VISITE::UNIQUE => 0,
            ],
            "RESECTION" => [
                VISITE::SCREEN => 45,
                VISITE::INCLUSION => 90,
                VISITE::SUIVI => 120,
                VISITE::FIN_ETUDE => 90,
                VISITE::MONITORAGE => 0,
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
            "SSL" => [
                VISITE::SCREEN => 0,
                VISITE::INCLUSION => 60,
                VISITE::SUIVI => 0,
                VISITE::FIN_ETUDE => 0,
                VISITE::MONITORAGE => 0,
                VISITE::UNIQUE => 60,
            ],
            "STRATEGE 2" => [
                VISITE::SCREEN => 60,
                VISITE::INCLUSION => 90,
                VISITE::SUIVI => 90,
                VISITE::FIN_ETUDE => 100,
                VISITE::MONITORAGE => 0,
                VISITE::UNIQUE => 0,
            ],
            "STRATEGIC-1" => [
                VISITE::SCREEN => 30,
                VISITE::INCLUSION => 90,
                VISITE::SUIVI => 180,
                VISITE::FIN_ETUDE => 150,
                VISITE::MONITORAGE => 120,
                VISITE::UNIQUE => 0,
            ],
            "SUNSTAR" => [
                VISITE::SCREEN => 30,
                VISITE::INCLUSION => 90,
                VISITE::SUIVI => 60,
                VISITE::FIN_ETUDE => 55,
                VISITE::MONITORAGE => 60,
                VISITE::UNIQUE => 0,
            ],
            "SURE" => [
                VISITE::SCREEN => 30,
                VISITE::INCLUSION => 55,
                VISITE::SUIVI => 120,
                VISITE::FIN_ETUDE => 120,
                VISITE::MONITORAGE => 0,
                VISITE::UNIQUE => 0,
            ],
            "THEODORA" => [
                VISITE::SCREEN => 30,
                VISITE::INCLUSION => 120,
                VISITE::SUIVI => 90,
                VISITE::FIN_ETUDE => 120,
                VISITE::MONITORAGE => 0,
                VISITE::UNIQUE => 0,
            ],
            "TMA POOL" => [
                VISITE::SCREEN => 120,
                VISITE::INCLUSION => 100,
                VISITE::SUIVI => 100,
                VISITE::FIN_ETUDE => 120,
                VISITE::MONITORAGE => 100,
                VISITE::UNIQUE => 120,
            ],
            "TOFAST" => [
                VISITE::SCREEN => 15,
                VISITE::INCLUSION => 45,
                VISITE::SUIVI => 75,
                VISITE::FIN_ETUDE => 30,
                VISITE::MONITORAGE => 30,
                VISITE::UNIQUE => 0,
            ],
            "TOPSATI" => [
                VISITE::SCREEN => 10,
                VISITE::INCLUSION => 75,
                VISITE::SUIVI => 75,
                VISITE::FIN_ETUDE => 60,
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
            "UPSTAND P20-410" => [
                VISITE::SCREEN => 5,
                VISITE::INCLUSION => 60,
                VISITE::SUIVI => 45,
                VISITE::FIN_ETUDE => 30,
                VISITE::MONITORAGE => 60,
                VISITE::UNIQUE => 0,
            ],
            "VIRTUS" => [
                VISITE::SCREEN => 0,
                VISITE::INCLUSION => 180,
                VISITE::SUIVI => 180,
                VISITE::FIN_ETUDE => 240,
                VISITE::MONITORAGE => 0,
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
        ];

        $updated_visites = [];
        $protocole_matched = [];
        foreach ($visites as $visite) {
            $date_visite = $visite->getDate();
            $date_min = (new DateTime("01/01/2019"))->setTime(0, 0, 0);


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
                    $protocole_matched[$essai]["nb_visite"] = isset($protocole_matched[$essai]["nb_visite"]) ? $protocole_matched[$essai]["nb_visite"] : 0;
                    $protocole_matched[$essai]["temps_total"] = isset($protocole_matched[$essai]["temps_total"]) ? $protocole_matched[$essai]["temps_total"] : 0;
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

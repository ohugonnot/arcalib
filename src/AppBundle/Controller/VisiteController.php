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

        dump($visites);

        return new Response();
    }
}

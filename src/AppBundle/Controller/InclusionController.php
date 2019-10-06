<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Inclusion;
use AppBundle\Factory\InclusionFactory;
use AppBundle\Services\CsvToArray;
use AppBundle\Services\SendMail;
use DateTime as DateTime;
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
class InclusionController extends Controller
{

    // ------------------------------------------Delete Inclusion-----------------------------------------------------
	/**
	 * @Route("/inclusion/supprimer/{id}", name="deleteInclusion", options={"expose"=true})
	 * @Security("has_role('ROLE_ARC')")
	 * @param Inclusion $inclusion
	 * @return JsonResponse
	 */
    public function deleteInclusionAction(Inclusion $inclusion)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($inclusion);
        $em->flush();

        return new JsonResponse(true);
    }

    // ------------------------------------------Liste Inclusion-----------------------------------------------------  

    /**
     * Todo : duplicate code search
     * @Route("/inclusions/", name="listeInclusions")
     * @param Request $request
     * @return Response
     */
    public function listeInclusionsAction(Request $request)
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

        $emInclusion = $em->getRepository(Inclusion::class);
        $query = $emInclusion->getQuery($user, $searchId, $search, [
            'statut' => $request->query->get("statut")
        ]);

        $paginator = $this->get('knp_paginator');
        $inclusions = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            20/*limit per page*/,
            array('defaultSortFieldName' => ['i.datInc'], 'defaultSortDirection' => 'desc')
        );

        return $this->render('inclusion/listeInclusions.html.twig', [
            'inclusions' => $inclusions
        ]);
    }

    /**
     * @Route("/inclusion/get/{id}", name="getInclusion", options={"expose"=true})
     * @param $id
     * @return JsonResponse
     */
    public function getInclusionAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $emInclusion = $em->getRepository(Inclusion::class);
        $inclusion = $emInclusion->findArray($id, $user);

        return new JsonResponse($inclusion);
    }

	/**
	 * @Route("/inclusion/save/{id}", name="saveInclusion", options={"expose"=true})
	 * @Security("has_role('ROLE_ARC')")
	 * @param Request $request
	 * @param null $id
	 * @param InclusionFactory $inclusionFactory
	 * @return JsonResponse
	 */
    public function saveInclusionAction(Request $request, InclusionFactory $inclusionFactory, $id = null)
    {
        $em = $this->getDoctrine()->getManager();
        $inclusion = $em->getRepository(Inclusion::class)->find($id);
        if (!$inclusion) {
            $inclusion = new Inclusion();
            $em->persist($inclusion);
            $new = true;
        }
        
        $inclusion = $inclusionFactory->hydrate($inclusion, $request->request->get("appbundle_inclusion"));

        if (isset($inclusion->errorsMessage) && $inclusion->errorsMessage)
            return new JsonResponse(["success" => false, "message" => $inclusion->errorsMessage]);

        if (isset($new) && $new) {
            $em->persist($inclusion);
            $mailer = $this->get(SendMail::class);
            $mailer->sendEmail("default", [
                'sujet' => "Création d'une inclusion",
                "inclusion" => $inclusion,
                "user" => $this->getUser()
            ]);
        }
        $em->flush();

        return new JsonResponse(["success" => true, "inclusion" => ["id" => $inclusion->getId()]]);
    }

    /**
     * @Route("/inclusion/editpartial/{id}", name="editInclusionPartial", options={"expose"=true})
     * @Security("has_role('ROLE_ARC')")
     * @param Request $request
     * @param Inclusion $inclusion
     * @return JsonResponse
     */
    public function editInclusionPartialAction(Request $request, Inclusion $inclusion)
    {
        $em = $this->getDoctrine()->getManager();

        $params = $request->request->get("appbundle_inclusion");
        $inclusion->setDatInc(DateTime::createFromFormat("d/m/Y", $params["datInc"]));
        $inclusion->setNumInc($params["numInc"]);
        $inclusion->setStatut($params["statut"]);

        $em->flush();

        return new JsonResponse(["success" => true, "inclusion" => ["id" => $inclusion->getId()]]);
    }

    /**
     * @Route("/inclusion/advanced/recherche/{query}", name="searchInclusions", options={"expose"=true})
     * @param Request $request
     * @param null $query
     * @return JsonResponse
     */
    public function searchInclusionsAction(Request $request, $query = null)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $query = explode(" ", $query);
        $filters = $request->request->get("filters");
        $emInclusion = $em->getRepository(Inclusion::class);
        $inclusions = $emInclusion->findAdvancedArray($query, $filters, $user);

        return new JsonResponse($inclusions);
    }

    /**
     * @Route("/inclusions/export", name="exportInclusions", options={"expose"=true})
     * @Security("has_role('ROLE_ADMIN')")
     * @param CsvToArray $export
     * @return StreamedResponse
     */
    public function exportInclusionsAction(CsvToArray $export)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $emInclusion = $em->getRepository(Inclusion::class);
        $inclusions = $emInclusion->findAllByUser($user);

        return $export->exportCSV($inclusions, "inclusions");
    }

    /**
     * @Route("/inclusions/export/all", name="exportInclusionsProtocole", options={"expose"=true})
     * @Security("has_role('ROLE_ADMIN')")
     * @param CsvToArray $export
     * @return StreamedResponse
     */
    public function exportInclusionsProtocoleAction(CsvToArray $export)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $emInclusion = $em->getRepository(Inclusion::class);
        $inclusions = $emInclusion->findAllByUser($user);

        return $export->exportCSV($inclusions, "inclusionsProtocole");
    }
}

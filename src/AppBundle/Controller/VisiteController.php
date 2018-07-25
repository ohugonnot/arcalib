<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Arc;
use AppBundle\Entity\Inclusion;
use AppBundle\Entity\Visite;
use AppBundle\Form\VisiteType;
use AppBundle\Services\CsvToArray;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/arcalib")
 */
class VisiteController extends Controller
{

//-----------------------------------DELETE VISITE  ->  deleteVisite-------------------------
    /**
     * @Route("/visite/supprimer/{id}", name="deleteVisite", options={"expose"=true})
     * @param $id
     * @return JsonResponse
     */
    public function deleteVisiteAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $emVisite = $em->getRepository(Visite::class);
        $visite = $emVisite->find($id);

        $em->remove($visite);
        $em->flush();

        return new JsonResponse(true);
    }


//-----------------------------------LISTE VISITE  ->  listeVisites-------------------------

    /**
     * @Route("/visites/", name="listeVisites")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
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
     * @return JsonResponse
     */
    public function saveVisitetAction(Request $request, $id = null)
    {
        $em = $this->getDoctrine()->getManager();
        $visite = $em->getRepository(Visite::class)->find($id);
        if (!$visite) {
            $visite = new Visite();
            $new = true;
        }

        $form = $this->get('form.factory')->create(VisiteType::class, $visite);
        $form->handleRequest($request);

        $inclusion_id = $request->request->get("inclusion");
        $inclusion = $em->getRepository(Inclusion::class)->find($inclusion_id);
        $visite->setInclusion($inclusion);

        $params = $request->request->get("appbundle_visite");

        $arc = $em->getRepository(Arc::class)->find($params["arc"]["id"]);
        $visite->setArc($arc);

        foreach ($params as $key => $value) {
            if (is_array($value) || $value == '') {
                unset($params[$key]);
            }
        }

        if (isset($params["date"])) {
            $date = \DateTime::createFromFormat('d/m/Y', $params["date"]);
            $visite->setDate($date);
        }

        if (isset($params["fact"]) and $params["fact"] == "true") {
            $visite->setFact(true);
        } else {
            $visite->setFact(false);
        }

        if (isset($new) && $new) {
            $em->persist($visite);
            $em->flush();
        } else {
            $em->flush();
        }

        return new JsonResponse(["success" => true, "visite" => ["id" => $visite->getId()]]);
    }


    /**
     * @param CsvToArray $csvToArray
     * @param bool $truncate
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Doctrine\Common\Persistence\Mapping\MappingException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function importAction(CsvToArray $csvToArray, $truncate = true)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        if ($truncate) {
            $em->createQuery('DELETE AppBundle:Visite v')->execute();
        }

        $file = $this->get('kernel')->getRootDir() . '/../bdd/visite.csv';
        $visites = $csvToArray->convert($file, ";");

        $bulkSize = 500;
        $i = 0;
        foreach ($visites as $p) {
            $i++;
            $visite = false;

            if (empty($p["N° inclusion table"]) || empty($p["Date visite"])) {
                continue;
            }

            foreach ($p as $k => $v) {
                $p[$k] = trim($v);
            }

            $date = \DateTime::createFromFormat('d/m/Y', $p["Date visite"]);
            $fact = (strtolower($p["Facturé"]) == "vrai") ? true : false;;

            if (!$date) {
                $date = null;
            }

            if (!$visite) {
                $visite = new Visite();
            }

            $visite->setDate($date);
            $visite->setType($p["Type visite"]);
            $visite->setCalendar($p["JMA"]);
            $visite->setStatut($p["Statut visite"]);
            $visite->setNote($p["Notes"]);
            $visite->setFact($fact);
            $visite->setNumFact($p["N° facture"]);

            if ($inclusion = $em->getRepository(Inclusion::class)->findOneBy(["idInterne" => $p["N° inclusion table"]])) {
                $visite->setInclusion($inclusion);
            }

            if ($arc = $em->getRepository(Arc::class)->findOneBy(["nomArc" =>$p["Arc référent"]])) {
                $visite->setArc($arc);
            }

            $em->persist($visite);;

            if ($i % $bulkSize == 0) {
                $em->flush();
                $em->clear();
            }
        }

        $em->flush();
        $em->clear();

        return $this->redirectToRoute("listeVisites");
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
     */
    public function searchVisitesAction(Request $request, $day = null, $month = null, $year = null, $query = null)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $date = new \DateTime();
        $date->setDate($year, $month, $day);
        $filters = $request->request->get("filters");
        $emVisite = $em->getRepository(Visite::class);
        $visites = $emVisite->findAdvancedArray($date, $filters, $user);

        return new JsonResponse($visites);
    }

}

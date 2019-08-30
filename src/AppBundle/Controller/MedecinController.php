<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Inclusion;
use AppBundle\Entity\Medecin;
use AppBundle\Form\MedecinType;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/arcalib")
 */
class MedecinController extends Controller
{
    /**
     * @Route("/medecin/ajouter", name="addMedecin")
     * @Security("has_role('ROLE_ARC')")
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function addMedecinAction(Request $request)
    {
        $medecin = new Medecin();

        $form = $this->get('form.factory')->create(MedecinType::class, $medecin);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($medecin);
            $em->flush();

            return $this->redirectToRoute("listeMedecins");
        }

        return $this->render('medecin/editMedecin.html.twig', [
            'form' => $form->createView(),
        ]);
    }

	/**
	 * @Route("/medecin/editer/{id}", name="editMedecin", options={"expose"=true})
	 * @Security("has_role('ROLE_ARC')")
	 * @param Request $request
	 * @param Medecin $medecin
	 * @return RedirectResponse|Response
	 */
    public function editMedecinAction(Request $request, Medecin $medecin)
    {
        $em = $this->getDoctrine()->getManager();
        $inclusions = $em->getRepository(Inclusion::class)->findAll();
        $inclusionMedecin = $em->getRepository(Inclusion::class)->findBy(['medecin' => $medecin]);
        $form = $this->get('form.factory')->create(MedecinType::class, $medecin);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {

            if (!$this->isGranted('ROLE_ARC')) {
                throw $this->createAccessDeniedException("Vous n'avez pas le droit d'éditer un Medecin");
            }

            $em->flush();
            return $this->redirectToRoute("listeMedecins");
        }

        return $this->render('medecin/editMedecin.html.twig', [
            'form' => $form->createView(),
            'informations' => ['total' => count($inclusions), 'medecin' => count($inclusionMedecin), 'essais' => $medecin->getEssais()]
        ]);
    }

	/**
	 * @Route("/medecin/supprimer/{id}", name="deleteMedecin", options={"expose"=true})
	 * @Security("has_role('ROLE_ARC')")
	 * @param Medecin $medecin
	 * @return RedirectResponse
	 */
    public function deleteMedecinAction(Medecin $medecin)
    {
        $em = $this->getDoctrine()->getManager();

        $em->remove($medecin);
        $em->flush();

        return $this->redirectToRoute("listeMedecins");
    }

    /**
     * @Route("/medecins/", name="listeMedecins", options={"expose"=true})
     * @param Request $request
     * @return Response
     */
    public function listeMedecinsAction(Request $request)
    {
        $search = $request->query->get("recherche");
        if ($search == null) {
            $search = '%%';
        }

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $emMedecin = $em->getRepository(Medecin::class);
        $user = $this->getUser();

        $query = $emMedecin->getQuery($user, $search);

        $paginator = $this->get('knp_paginator');
        $medecins = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            20/*limit per page*/,
            array('defaultSortFieldName' => ['m.nom', 'm.prenom'], 'defaultSortDirection' => 'asc')
        );

        return $this->render('medecin/listeMedecins.html.twig', [
            'medecins' => $medecins
        ]);
    }

    /**
     * @Route("/medecins/advanced/recherche/{query}", name="searchMedecins", options={"expose"=true})
     * @param null $query
     * @return JsonResponse
     */
    public function searchMedecinsAction($query = null)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $emMedecin = $em->getRepository(Medecin::class);
        $medecins = $emMedecin->findAdvancedArray($query, $user);

        return new JsonResponse($medecins);
    }
}
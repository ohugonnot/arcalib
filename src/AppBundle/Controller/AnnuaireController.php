<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Annuaire;
use AppBundle\Form\AnnuaireType;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/arcalib")
 */
class AnnuaireController extends Controller
{

    // ------------------------------------------ADD ANNUAIRE-----------------------------------------------------
    /**
     * @Route("/annuaire/ajouter", name="addAnnuaire")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function addAnnuaireAction(Request $request)
    {
        $annuaire = new Annuaire();
        $form = $this->get('form.factory')->create(AnnuaireType::class, $annuaire);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($annuaire);
            $em->flush();

            return $this->redirectToRoute("listeAnnuaires");
        }

        return $this->render('annuaire/addAnnuaire.html.twig', [
            'form' => $form->createView(),
        ]);
    }

// ------------------------------------------Edit ANNUAIRE-----------------------------------------------------

    /**
     * @Route("/annuaire/editer/{id}", name="editAnnuaire", options={"expose"=true})
     * @param Request $request
     * @param Annuaire $annuaire
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editAnnuaireAction(Request $request, Annuaire $annuaire)
    {
        $form = $this->get('form.factory')->create(AnnuaireType::class, $annuaire);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return $this->redirectToRoute("listeAnnuaires");
        }

        return $this->render('annuaire/addAnnuaire.html.twig', [
            'form' => $form->createView(),
        ]);
    }

// ------------------------------------------delete ANNUAIRE-----------------------------------------------------

    /**
     * @Route("/annuaire/supprimer/{id}", name="deleteAnnuaire", options={"expose"=true})
     * @Security("has_role('ROLE_ARC')")
     * @param Annuaire $annuaire
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteAnnuaireAction(Annuaire $annuaire)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($annuaire);
        $em->flush();

        return $this->redirectToRoute("listeAnnuaires");
    }


// ------------------------------------------Liste ANNUAIRE-----------------------------------------------------

    /**
     * @Route("/annuaires/", name="listeAnnuaires", options={"expose"=true})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listeAnnuairesAction(Request $request)
    {
        $search = $request->query->get("recherche");
        if ($search == null) {
            $search = '%%';
        }

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $emAnnuaire = $em->getRepository(Annuaire::class);
        $query = $emAnnuaire->getQuery($search);

        $paginator = $this->get('knp_paginator');
        $annuaires = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            20/*limit per page*/,
            array('defaultSortFieldName' => ['a.nom'], 'defaultSortDirection' => 'asc')
        );

        return $this->render('annuaire/listeAnnuaires.html.twig', [
            'annuaires' => $annuaires
        ]);
    }

// ------------------------------------------Search ANNUAIRE-----------------------------------------------------

    /**
     * @Route("/annuaire/recherche/{query}", name="searchAnnuaires", options={"expose"=true})
     * @param null $query
     * @return JsonResponse
     */
    public function searchAnnuairesAction($query = null)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $query = explode(" ", $query);
        $emAnnuaire = $em->getRepository('AppBundle:Annuaire');
        $annuaires = $emAnnuaire->findAdvancedArray($query);

        return new JsonResponse($annuaires);
    }
}

<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Actualite;
use AppBundle\Form\ActualiteType;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;


/**
 * @Route("/arcalib")
 */
class ActualiteController extends Controller
{

// ------------------------------------------ADD ACTUALITE----------------------------------------------------- 
    /**
     * @Route("/actualite/ajouter", name="addActualite")
     * @Security("has_role('ROLE_ADMIN')")
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function addActualiteAction(Request $request)
    {
        $actualite = new Actualite();

        $form = $this->get('form.factory')->create(ActualiteType::class, $actualite);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->persist($actualite);
            $em->flush();

            return $this->redirectToRoute("listeActualites");
        }

        return $this->render('actualite/addActualite.html.twig', [
            'form' => $form->createView(),
        ]);
    }

// ------------------------------------------Edit ACTUALITE----------------------------------------------------- 

    /**
     * @Route("/actualite/editer/{id}", name="editActualite")
     * @Security("has_role('ROLE_ADMIN')")
     * @param Request $request
     * @param Actualite $actualite
     * @return RedirectResponse|Response
     */
    public function editActualiteAction(Request $request, Actualite $actualite)
    {
        $form = $this->get('form.factory')->create(ActualiteType::class, $actualite);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return $this->redirectToRoute("listeActualites");
        }

        return $this->render('actualite/addActualite.html.twig', [
            'form' => $form->createView(),
        ]);
    }

// ------------------------------------------Supp ACTUALITE-----------------------------------------------------

    /**
     * @Route("/actualite/supprimer/{id}", name="deleteActualite", options={"expose"=true})
     * @Security("has_role('ROLE_ADMIN')")
     * @param Actualite $actualite
     * @return RedirectResponse
     */
    public function deleteActualiteAction(Actualite $actualite)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($actualite);
        $em->flush();

        return $this->redirectToRoute("listeActualites");
    }

// ------------------------------------------Liste ACTUALITE-----------------------------------------------------

    /**
     * @Route("/actualites/", name="listeActualites", options={"expose"=true})
     * @param Request $request
     * @return Response
     */
    public function listeActualitesAction(Request $request)
    {
        $search = $request->query->get("recherche");
        if ($search == null) {
            $search = '%%';
        }

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $emActualite = $em->getRepository(Actualite::class);
        $query = $emActualite->getQuery($search);

        $paginator = $this->get('knp_paginator');
        $actualites = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            20/*limit per page*/,
            array('defaultSortFieldName' => ['a.date'], 'defaultSortDirection' => 'desc')
        );

        return $this->render('actualite/listeActualites.html.twig', [
            'actualites' => $actualites
        ]);
    }
}

<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Arc;
use AppBundle\Form\ArcType;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\OptimisticLockException;
use Doctrine\ORM\ORMException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/arcalib")
 */
class ArcController extends Controller
{
// ------------------------------------------ADD ARC-----------------------------------------------------  
    /**
     * @Route("/arc/ajouter", name="addArc")
     * @Security("has_role('ROLE_ADMIN')")
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function addArcAction(Request $request)
    {
        $arc = new Arc();
        $form = $this->get('form.factory')->create(ArcType::class, $arc);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($arc);
            $em->flush();

            return $this->redirectToRoute("listeArcs");
        }

        return $this->render('arc/editArc.html.twig', [
            'form' => $form->createView(),
        ]);
    }

// ------------------------------------------EDIT ARC-----------------------------------------------------
    /**
     * @Route("/arc/editer/{id}", name="editArc")
     * @Security("has_role('ROLE_ADMIN')")
     * @param Request $request
     * @param Arc $arc
     * @return RedirectResponse|Response
     */
    public function editArcAction(Request $request, Arc $arc)
    {
        $form = $this->get('form.factory')->create(ArcType::class, $arc);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return $this->redirectToRoute("listeArcs");
        }

        return $this->render('arc/editArc.html.twig', [
            'form' => $form->createView(),
        ]);
    }

// ------------------------------------------SUPP ARC-----------------------------------------------------
    /**
     * @Route("/arc/delete/{id}", name="deleteArc", options={"expose"=true})
     * @Security("has_role('ROLE_ADMIN')")
     * @param Arc $arc
     * @return RedirectResponse
     * @throws ORMException
     * @throws OptimisticLockException
     */
    public function deleteArcAction(Arc $arc)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $em->remove($arc);
        $em->flush();

        return $this->redirectToRoute("listeArcs");
    }

// ------------------------------------------LISTE ARC-----------------------------------------------------
    /**
     * @Route("/arcs/", name="listeArcs", options={"expose"=true})
     * @param Request $request
     * @return Response
     */
    public function listeArcsAction(Request $request)
    {
        $search = $request->query->get("recherche");
        if (!$search)
            $search = '%%';

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $emArc = $em->getRepository(Arc::class);
        $query = $emArc->getQuery($search);

        $paginator = $this->get('knp_paginator');
        $arcs = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            20/*limit per page*/,
            array('defaultSortFieldName' => ['a.nomArc','a.prenomArc'], 'defaultSortDirection' => 'asc')
        );

        return $this->render('arc/listeArcs.html.twig', [
            'arcs' => $arcs
        ]);
    }
}

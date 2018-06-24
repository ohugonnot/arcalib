<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Inclusion;
use AppBundle\Entity\Traitement;
use AppBundle\Form\TraitementType;
use Doctrine\Common\Collections\ArrayCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/arcalib")
 */
class TraitementController extends Controller
{

    // ------------------------------------------ADD Document-----------------------------------------------------
    /**
     * @Route("/traitements/inclusion/{id}/ajouter", name="addTraitement", options={"expose"=true})
     * @Security("has_role('ROLE_ARC')")
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response|\Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function addTraitementAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $inclusion = $em->getRepository(Inclusion::class)->find($id);

        if(!$inclusion) {
            return $this->createNotFoundException("L'inclusion $id n'a pas été trouvé");
        }

        $traitement = new Traitement();
        $traitement->setInclusion($inclusion);

        $form = $this->get('form.factory')->create(TraitementType::class, $traitement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->persist($traitement);
            $em->flush();

            return $this->redirectToRoute("editTraitement", ["id" => $traitement->getId()]);
        }

        $emTraitement = $em->getRepository(Traitement::class);
        $allTraitements = new ArrayCollection($emTraitement->findBy(["inclusion" => $inclusion], ["attributionAt" => "ASC"]));

        return $this->render('traitement/editTraitement.html.twig', [
            'form' => $form->createView(),
            'allTraitements' => $allTraitements
        ]);
    }

    /**
     * @Route("/traitements/editer/{id}", name="editTraitement", options={"expose"=true})
     * @param Request $request
     * @param Traitement $traitement
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editTraitementAction(Request $request, Traitement $traitement)
    {
        $em = $this->getDoctrine()->getManager();
        $emTraitement = $em->getRepository(Traitement::class);

        if (!$traitement) {
            throw $this->createNotFoundException("Le traitement ".$traitement->getId()." n'existe pas.");
        }

        $inclusion = $traitement->getInclusion();

        $form = $this->get('form.factory')->create(TraitementType::class, $traitement);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if (!$this->get('security.authorization_checker')->isGranted('ROLE_ARC')) {
                throw $this->createAccessDeniedException('Vous n\'avez pas le droit de sauvegarder !');
            }
            $em->flush();

            return $this->redirectToRoute("listeTraitements", ["id" => $inclusion->getId()]);
        }

        $allTraitements = new ArrayCollection($emTraitement->findBy(["inclusion" => $inclusion], ["attributionAt" => "ASC"]));
        if ($allTraitements->contains($traitement)) {
            $index = $allTraitements->indexOf($traitement);
            $prev = $allTraitements->get($index - 1);
            $next = $allTraitements->get($index + 1);
        }

        return $this->render('traitement/editTraitement.html.twig', [
            'form' => $form->createView(),
            'prev' => $prev ?? null,
            'next' => $next ?? null,
            'count' => $allTraitements->count(),
            'index' => $index ?? null,
            'allTraitements' => $allTraitements,
        ]);
    }

    /**
     * @Route("/traitement/inclusion/{id}/voir", name="voirTraitement", options={"expose"=true})
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response|RedirectResponse
     */
    public function firstTraitementAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $inclusion = $em->getRepository(Inclusion::class)->find($id);

        if (!$inclusion) {
            throw $this->createNotFoundException("L'inclusion $id n'existe pas.");
        }

        $emTraitement = $em->getRepository(Traitement::class);
        $allTraitements = new ArrayCollection($emTraitement->findBy(["inclusion" => $inclusion], ["attributionAt" => "ASC"]));

        if (!$allTraitements->isEmpty()) {
            return $this->redirectToRoute('editTraitement', ["id" => $allTraitements->first()->getId()], 301);
        }
        return $this->forward("AppBundle:Traitement:listeTraitementInclusion", [
            "id" => $id
        ]);
    }

    /**
     * @Route("/traitements/supprimer/{id}", name="deleteTraitement", options={"expose"=true})
     * @Security("has_role('ROLE_ADMIN')")
     * @param Traitement $traitement
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteDocumentAction(Traitement $traitement)
    {
        $em = $this->getDoctrine()->getManager();
        $inclusion = $traitement->getInclusion();

        $em->remove($traitement);
        $em->flush();

        return $this->redirectToRoute("listeTraitements", ["id" => $inclusion->getId()]);
    }

    /**
     * @Route("/traitements/inclusion/{id}", name="listeTraitements", options={"expose"=true})
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listeTraitementInclusionAction(Request $request, $id)
    {
        $search = $request->query->get("recherche");
        if ($search == null) {
            $search = '%%';
        }

        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $emTraitement = $em->getRepository(Traitement::class);
        $inclusion = $em->getRepository(Inclusion::class)->find($id);

        if (!$inclusion) {
            throw $this->createNotFoundException("L'inclusion $id n'existe pas.");
        }

        $query = $emTraitement->getQuery($user, $search, $id);

        $paginator = $this->get('knp_paginator');
        $traitements = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            20/*limit per page*/,
            array('defaultSortFieldName' => ['t.attributionAt'], 'defaultSortDirection' => 'ASC')
        );

        return $this->render('traitement/listeTraitements.html.twig', [
            'traitements' => $traitements,
            'inclusion' => $inclusion,
        ]);
    }
}
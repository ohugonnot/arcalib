<?php

namespace AppBundle\Controller;

use AppBundle\Entity\CTCAESoc;
use AppBundle\Entity\CTCAETerm;
use AppBundle\Entity\Inclusion;
use AppBundle\Entity\EI;
use AppBundle\Form\EIType;
use Doctrine\Common\Collections\ArrayCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @Route("/arcalib")
 */
class EiController extends Controller
{

    // ------------------------------------------ADD Document-----------------------------------------------------
	/**
	 * @Route("/eis/inclusion/{id}/ajouter", name="addEi", options={"expose"=true})
	 * @Security("has_role('ROLE_ARC')")
	 * @param Request $request
	 * @param Inclusion $inclusion
	 * @return RedirectResponse|Response|NotFoundHttpException
	 */
    public function addEiAction(Request $request, Inclusion $inclusion)
    {
        $em = $this->getDoctrine()->getManager();

        $ei = new Ei();
        $ei->setInclusion($inclusion);

        $form = $this->get('form.factory')->create(EiType::class, $ei);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->persist($ei);
            $em->flush();

            return $this->redirectToRoute("editEi", ["id" => $ei->getId()]);
        }

        $emEi = $em->getRepository(Ei::class);
        $allEis = new ArrayCollection($emEi->findBy(["inclusion" => $inclusion], ["debutAt" => "ASC"]));

        return $this->render('ei/editEi.html.twig', [
            'form' => $form->createView(),
	        'prev' => $prev ?? null,
	        'next' => $next ?? null,
	        'count' => $allEis->count(),
	        'index' => $index ?? null,
        ]);
    }

    /**
     * @Route("/eis/soc/{id}/terms", name="getTerms", options={"expose"=true})
     * @param CTCAESoc $CTCAESoc
     * @return JsonResponse
     */
    public function getTermsAction(CTCAESoc $CTCAESoc)
    {
        $terms = $CTCAESoc->getTerms();
        $options = [];
        $options[] = ["value" => '', "text" => ''];
        foreach($terms as $term) {
            $options[] = ["value" => $term->getId(), "text" => $term->getNom()];
        }

        return new JsonResponse($options);
    }

    /**
     * @Route("/eis/term/{id}/grades", name="getGrades", options={"expose"=true})
     * @param CTCAETerm $CTCAETerm
     * @return JsonResponse
     */
    public function geGradesAction(CTCAETerm $CTCAETerm)
    {
        $grades = $CTCAETerm->getGrades();
        $options = [];
        $options[] = ["value" => '', "text" => ''];
        foreach($grades as $grade) {
            $options[] = ["value" => $grade->getId(), "text" => $grade->getGrade().' - '.$grade->getNom()];
        }

        return new JsonResponse($options);
    }

    /**
     * @Route("/eis/editer/{id}", name="editEi", options={"expose"=true})
     * @param Request $request
     * @param Ei $ei
     * @return RedirectResponse|Response
     */
    public function editEiAction(Request $request, Ei $ei)
    {
        $em = $this->getDoctrine()->getManager();
        $emEi = $em->getRepository(Ei::class);
        $inclusion = $ei->getInclusion();

        $form = $this->get('form.factory')->create(EiType::class, $ei);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if (!$this->get('security.authorization_checker')->isGranted('ROLE_ARC')) {
                throw $this->createAccessDeniedException('Vous n\'avez pas le droit de sauvegarder !');
            }
            $em->flush();

            return $this->redirectToRoute("editEi", ["id" => $ei->getId()]);
        }

        $allEis = new ArrayCollection($emEi->findBy(["inclusion" => $inclusion], ["debutAt" => "ASC"]));
	    if ($allEis->contains($ei)) {
		    $index = $allEis->indexOf($ei);
		    $prev = $allEis->get($index - 1);
		    $next = $allEis->get($index + 1);
	    }

        return $this->render('ei/editEi.html.twig', [
            'form' => $form->createView(),
            'prev' => $prev ?? null,
            'next' => $next ?? null,
            'count' => $allEis->count(),
            'index' => $index ?? null,
            'allEis' => $allEis,
        ]);
    }

	/**
	 * @Route("/ei/inclusion/{id}/voir", name="voirEi", options={"expose"=true})
	 * @param Inclusion $inclusion
	 * @return Response|RedirectResponse
	 */
    public function firstEiAction(Inclusion $inclusion)
    {
        $em = $this->getDoctrine()->getManager();
        $emEi = $em->getRepository(Ei::class);
        $allEis = new ArrayCollection($emEi->findBy(["inclusion" => $inclusion], ["debutAt" => "ASC"]));

        if (!$allEis->isEmpty()) {
            return $this->redirectToRoute('editEi', ["id" => $allEis->first()->getId()], 301);
        }
        return $this->forward("AppBundle:Ei:listeEiInclusion", [
            "id" => $inclusion->getId()
        ]);
    }

    /**
     * @Route("/eis/supprimer/{id}", name="deleteEi", options={"expose"=true})
     * @Security("has_role('ROLE_ADMIN')")
     * @param Ei $ei
     * @return RedirectResponse
     */
    public function deleteDocumentAction(Ei $ei)
    {
        $em = $this->getDoctrine()->getManager();
        $inclusion = $ei->getInclusion();

        $em->remove($ei);
        $em->flush();

        return $this->redirectToRoute("listeEis", ["id" => $inclusion->getId()]);
    }

	/**
	 * @Route("/eis/inclusion/{id}", name="listeEis", options={"expose"=true})
	 * @param Request $request
	 * @param Inclusion $inclusion
	 * @return Response
	 */
    public function listeEiInclusionAction(Request $request, Inclusion $inclusion)
    {
        $search = $request->query->get("recherche");
        if ($search == null) {
            $search = '%%';
        }

        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $emEi = $em->getRepository(Ei::class);

        $query = $emEi->getQuery($user, $search, $inclusion->getId());

        $paginator = $this->get('knp_paginator');
        $eis = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            20/*limit per page*/,
            array('defaultSortFieldName' => ['ei.debutAt'], 'defaultSortDirection' => 'ASC')
        );

        return $this->render('ei/listeEis.html.twig', [
            'eis' => $eis,
            'inclusion' => $inclusion,
        ]);
    }
}
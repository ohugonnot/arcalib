<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Tag;
use AppBundle\Form\TagType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/arcalib")
 */
class TagController extends Controller
{

    // ------------------------------------------ADD TAG----------------------------------------------------- 
    /**
     * @Route("/tag/ajouter", name="addTag")
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function addTagAction(Request $request)
    {
        $tag = new Tag();

        $form = $this->get('form.factory')->create(TagType::class, $tag);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($tag);
            $em->flush();

            return $this->redirectToRoute("listeTags");
        }

        return $this->render('tag/addTag.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // ------------------------------------------Edit TAG----------------------------------------------------- 

	/**
	 * @Route("/tag/editer/{id}", name="editTag")
	 * @param Request $request
	 * @param Tag $tag
	 * @return RedirectResponse|Response
	 */
    public function editTagAction(Request $request, Tag $tag)
    {
        $em = $this->getDoctrine()->getManager();
        $form = $this->get('form.factory')->create(TagType::class, $tag);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {

            if (!$this->isGranted('ROLE_ARC')) {
                throw $this->createAccessDeniedException("Vous n'avez pas le droit d'éditer un Tag");
            }

            $em->flush();

            return $this->redirectToRoute("listeTags");
        }

        return $this->render('tag/addTag.html.twig', [
            'form' => $form->createView(),
            'essais' => $tag->getEssais()
        ]);
    }

    // ------------------------------------------Supp TAG-----------------------------------------------------

	/**
	 * @Route("/tag/supprimer/{id}", name="deleteTag", options={"expose"=true})
	 * @Security("has_role('ROLE_ARC')")
	 * @param Tag $tag
	 * @return RedirectResponse
	 */
    public function deleteTagAction(Tag $tag)
    {
        $em = $this->getDoctrine()->getManager();

        $em->remove($tag);
        $em->flush();

        return $this->redirectToRoute("listeTags");
    }

    // ------------------------------------------Liste TAG-----------------------------------------------------

    /**
     * @Route("/tags/", name="listeTags", options={"expose"=true})
     * @param Request $request
     * @return Response
     */
    public function listeTagsAction(Request $request)
    {
        $search = $request->query->get("recherche");
        if ($search == null) {
            $search = '%%';
        }

        $em = $this->getDoctrine()->getManager();
        $emTag = $em->getRepository(Tag::class);
        $query = $emTag->getQuery($search);

        $paginator = $this->get('knp_paginator');
        $tags = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            20/*limit per page*/,
            array('defaultSortFieldName' => ['t.nom', 't.classe'], 'defaultSortDirection' => 'asc')
        );

        return $this->render('tag/listeTags.html.twig', [
            'tags' => $tags
        ]);
    }
}

<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Inclusion;
use AppBundle\Entity\Event;
use AppBundle\Form\EventType;
use Doctrine\Common\Collections\ArrayCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

/**
 * @Route("/arcalib")
 */
class EventController extends Controller
{

    // ------------------------------------------ADD Document-----------------------------------------------------
	/**
	 * @Route("/events/inclusion/{id}/ajouter", name="addEvent", options={"expose"=true})
	 * @Security("has_role('ROLE_ARC')")
	 * @param Request $request
	 * @param Inclusion $inclusion
	 * @return RedirectResponse|Response|NotFoundHttpException
	 */
    public function addEventAction(Request $request, Inclusion $inclusion)
    {
        $em = $this->getDoctrine()->getManager();
        $event = new Event();
        $event->setInclusion($inclusion);

        $form = $this->get('form.factory')->create(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $em->persist($event);
            $em->flush();

            return $this->redirectToRoute("editEvent", ["id" => $event->getId()]);
        }

        $emEvent = $em->getRepository(Event::class);
        $allEvents = new ArrayCollection($emEvent->findBy(["inclusion" => $inclusion], ["date" => "ASC"]));

        return $this->render('event/editEvent.html.twig', [
            'form' => $form->createView(),
            'allEvents' => $allEvents
        ]);
    }

    /**
     * @Route("/events/editer/{id}", name="editEvent", options={"expose"=true})
     * @param Request $request
     * @param Event $event
     * @return RedirectResponse|Response
     */
    public function editEventAction(Request $request, Event $event)
    {
        $em = $this->getDoctrine()->getManager();
        $emEvent = $em->getRepository(Event::class);
        $inclusion = $event->getInclusion();

        $form = $this->get('form.factory')->create(EventType::class, $event);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            if (!$this->get('security.authorization_checker')->isGranted('ROLE_ARC')) {
                throw $this->createAccessDeniedException('Vous n\'avez pas le droit de sauvegarder !');
            }
            $em->flush();

            return $this->redirectToRoute("listeEvents", ["id" => $inclusion->getId()]);
        }

        $allEvents = new ArrayCollection($emEvent->findBy(["inclusion" => $inclusion], ["date" => "ASC"]));
        if ($allEvents->contains($event)) {
            $index = $allEvents->indexOf($event);
            $prev = $allEvents->get($index - 1);
            $next = $allEvents->get($index + 1);
        }

        return $this->render('event/editEvent.html.twig', [
            'form' => $form->createView(),
            'prev' => $prev ?? null,
            'next' => $next ?? null,
            'count' => $allEvents->count(),
            'index' => $index ?? null,
            'allEvents' => $allEvents,
        ]);
    }

	/**
	 * @Route("/event/inclusion/{id}/voir", name="voirEvent", options={"expose"=true})
	 * @param Inclusion $inclusion
	 * @return Response|RedirectResponse
	 */
    public function firstEventAction(Inclusion $inclusion)
    {
        $em = $this->getDoctrine()->getManager();
        $emEvent = $em->getRepository(Event::class);
        $allEvents = new ArrayCollection($emEvent->findBy(["inclusion" => $inclusion], ["date" => "ASC"]));

        if (!$allEvents->isEmpty()) {
            return $this->redirectToRoute('editEvent', ["id" => $allEvents->first()->getId()], 301);
        }
        return $this->forward("AppBundle:Event:listeEventInclusion", [
            "id" => $inclusion->getId()
        ]);
    }

    /**
     * @Route("/events/supprimer/{id}", name="deleteEvent", options={"expose"=true})
     * @Security("has_role('ROLE_ADMIN')")
     * @param Event $event
     * @return RedirectResponse
     */
    public function deleteDocumentAction(Event $event)
    {
        $em = $this->getDoctrine()->getManager();
        $inclusion = $event->getInclusion();

        $em->remove($event);
        $em->flush();

        return $this->redirectToRoute("listeEvents", ["id" => $inclusion->getId()]);
    }

	/**
	 * @Route("/events/inclusion/{id}", name="listeEvents", options={"expose"=true})
	 * @param Request $request
	 * @param Inclusion $inclusion
	 * @return Response
	 */
    public function listeEventInclusionAction(Request $request, Inclusion $inclusion)
    {
        $search = $request->query->get("recherche");
        if ($search == null) {
            $search = '%%';
        }

        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $emEvent = $em->getRepository(Event::class);

        $query = $emEvent->getQuery($user, $search, $inclusion->getId());

        $paginator = $this->get('knp_paginator');
        $events = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            20/*limit per page*/,
            array('defaultSortFieldName' => ['ev.date'], 'defaultSortDirection' => 'ASC')
        );

        return $this->render('event/listeEvents.html.twig', [
            'events' => $events,
            'inclusion' => $inclusion,
        ]);
    }
}
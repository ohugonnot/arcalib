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
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response|\Symfony\Component\HttpKernel\Exception\NotFoundHttpException
     */
    public function addEventAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $inclusion = $em->getRepository(Inclusion::class)->find($id);

        if(!$inclusion) {
            return $this->createNotFoundException("L'inclusion $id n'a pas été trouvé");
        }

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
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editEventAction(Request $request, Event $event)
    {
        $em = $this->getDoctrine()->getManager();
        $emEvent = $em->getRepository(Event::class);

        if (!$event) {
            throw $this->createNotFoundException("Le event ".$event->getId()." n'existe pas.");
        }

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
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response|RedirectResponse
     */
    public function firstEventAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $inclusion = $em->getRepository(Inclusion::class)->find($id);

        if (!$inclusion) {
            throw $this->createNotFoundException("L'inclusion $id n'existe pas.");
        }

        $emEvent = $em->getRepository(Event::class);
        $allEvents = new ArrayCollection($emEvent->findBy(["inclusion" => $inclusion], ["date" => "ASC"]));

        if (!$allEvents->isEmpty()) {
            return $this->redirectToRoute('editEvent', ["id" => $allEvents->first()->getId()], 301);
        }
        return $this->forward("AppBundle:Event:listeEventInclusion", [
            "id" => $id
        ]);
    }

    /**
     * @Route("/events/supprimer/{id}", name="deleteEvent", options={"expose"=true})
     * @Security("has_role('ROLE_ADMIN')")
     * @param Event $event
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
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
     * @param $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listeEventInclusionAction(Request $request, $id)
    {
        $search = $request->query->get("recherche");
        if ($search == null) {
            $search = '%%';
        }

        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $emEvent = $em->getRepository(Event::class);
        $inclusion = $em->getRepository(Inclusion::class)->find($id);

        if (!$inclusion) {
            throw $this->createNotFoundException("L'inclusion $id n'existe pas.");
        }

        $query = $emEvent->getQuery($user, $search, $id);

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
<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Todo;
use AppBundle\Form\TodoType;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Cookie;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/arcalib")
 */
class TodoController extends Controller
{

    /**
     * @Route("/todos/", name="listeTodos", options={"expose"=true})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listeTodosAction(Request $request)
    {
        $paginator = $this->get('knp_paginator');

        $search = $request->query->get("recherche");
        if ($search == null) {
            $search = '%%';
        }

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $emTodo = $em->getRepository(Todo::class);
        $queryBuilder = $emTodo->createQueryBuilder("t")
            ->leftJoin("t.auteur", "a")
            ->leftJoin("t.destinataires", "d")
            ->where("(t.importance like :search or t.titre like :search or t.texte like :search or t.niveauResolution like :search  or t.dateAlerte like :search or t.dateFin like :search or d.username like :search or d.email like :search  or a.username like :search or a.email like :search)")
            ->addSelect("d", "a")
            ->setParameter("search", '%'. $search . '%');

        if ($request->query->get("resolu") != null && $request->query->get("resolu")) {
            $queryBuilder->andWhere("t.niveauResolution != '" . Todo::RESOLU . "' and t.niveauResolution != '" . Todo::RESOLU_AVEC_REMARQUES . "'");
        }

        if ($this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {

            $todos = $paginator->paginate(
                $queryBuilder->getQuery(), /* query NOT result */
                $request->query->getInt('page', 1)/*page number*/,
                20/*limit per page*/,
                array('defaultSortFieldName' => ['t.dateFin'], 'defaultSortDirection' => 'ASC', "wrap-queries" => true, "anchor" => '#block-notes-all')
            );
        }

        $myTodosBuilder = clone $queryBuilder;
        $myTodosBuilder->andWhere("a = :user")->setParameter("user", $this->getUser());

        $myTodos = $paginator->paginate(
            $myTodosBuilder->getQuery(), /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            20/*limit per page*/,
            array('defaultSortFieldName' => ['t.dateFin'], 'defaultSortDirection' => 'ASC', "wrap-queries" => true, "anchor" => '#block-notes-envoyes')
        );

        $attribuedTodoBuilder = clone $queryBuilder;
        $attribuedTodoBuilder->andWhere("d = :user")->setParameter("user", $this->getUser());

        $attribuedTodos = $paginator->paginate(
            $attribuedTodoBuilder->getQuery(), /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            20/*limit per page*/,
            array('defaultSortFieldName' => ['t.dateFin'], 'defaultSortDirection' => 'ASC', "wrap-queries" => true, "anchor" => '#block-notes-recus')
        );

        $response = $this->render('todo/listeTodos.html.twig', [
            'todos' => (isset($todos)) ? $todos : [],
            'myTodos' => $myTodos,
            'attribuedTodos' => $attribuedTodos,
        ]);

        $response->headers->setCookie(new Cookie('lastVisite', (new \DateTime())->format("Y-m-d  H:i:s"), time() + 3600 * 24 * 7));

        return $response;
    }

    // ------------------------------------------ADD Todo -----------------------------------------------------

    /**
     * @Route("/todo/ajouter", name="addTodo")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function addTodoAction(Request $request)
    {
        $todo = new Todo();

        $form = $this->get('form.factory')->create(TodoType::class, $todo);
        $form->remove("auteur");

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $todo->setAuteur($this->getUser())->setCreatedAt(new \DateTime());
            $em->persist($todo);
            $em->flush();

            return $this->redirectToRoute("listeTodos");
        }

        return $this->render('todo/editTodo.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // ------------------------------------------Edit TODO-----------------------------------------------------

	/**
	 * @Route("/todo/editer/{id}", name="editTodo")
	 * @param Request $request
	 * @param Todo $todo
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
	 */
    public function editTodoAction(Request $request, Todo $todo)
    {
        $em = $this->getDoctrine()->getManager();

        /** @var ArrayCollection $destinaires */
        $destinaires = $todo->getDestinataires();

        // Si tu es admin ou auteur ou que tu fais partie des destinataires tu passes sinon tu es refusé
        if ($this->getUser() != $todo->getAuteur()
            && !$this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')
            && !$destinaires->contains($this->getUser())) {

            throw $this->createAccessDeniedException("Vous n'avez pas le droit d'accèder à cette page");
        }

        $form = $this->get('form.factory')->create(TodoType::class, $todo);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->flush();

            return $this->redirectToRoute("listeTodos");
        }

        return $this->render('todo/editTodo.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // ------------------------------------------delete TODO-----------------------------------------------------

	/**
	 * @Route("/todo/supprimer/{id}", name="deleteTodo", options={"expose"=true})
	 * @param Todo $todo
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse
	 */
    public function deleteTodoAction(Todo $todo)
    {
        $em = $this->getDoctrine()->getManager();

        if ($this->getUser() != $todo->getAuteur() && !$this->container->get('security.authorization_checker')->isGranted('ROLE_ADMIN')) {
            throw $this->createAccessDeniedException("Vous n'avez pas le droit d'accèder à cette page");
        }

        $em->remove($todo);;
        $em->flush();

        return $this->redirectToRoute("listeTodos");
    }
}

<?php

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Form\UserTypeAdmin;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/arcalib")
 */
class UserController extends Controller
{

// ------------------------------------------ADD USER----------------------------------------------------- 
    /**
     * @Route("/utilisateur/ajouter", name="addUser")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function addUserAction(Request $request)
    {
        $user = new User();

        $form = $this->get('form.factory')->create(UserTypeAdmin::class, $user);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->persist($user);
            $em->flush();

            return $this->redirectToRoute("listeUsers");
        }

        return $this->render('@FOSUser/Profile/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

// ------------------------------------------Edit USER----------------------------------------------------- 

    /**
     * @Route("/utilisateur/editer/{id}", name="editUser")
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editUserAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $emUser = $em->getRepository(User::class);
        $user = $emUser->find($id);

        $form = $this->get('form.factory')->create(UserTypeAdmin::class, $user);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $userManager = $this->get('fos_user.user_manager');
            $userManager->updateUser($user);
            $em->flush();

            $this->addFlash(
                'success',
                'Les informations de l\'utilisateur ont bien été modifiées.'
            );

            return $this->redirectToRoute("listeUsers");
        }

        return $this->render('@FOSUser/Profile/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

// ------------------------------------------Supp USER-----------------------------------------------------

    /**
     * @Route("/utilisateur/supprimer/{id}", name="deleteUser", options={"expose"=true})
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteUserAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $emUser = $em->getRepository(User::class);
        $user = $emUser->find($id);

        $em->remove($user);
        $em->flush();

        return $this->redirectToRoute("listeUsers");
    }

// ------------------------------------------Liste USER-----------------------------------------------------

    /**
     * @Route("/utilisateurs/", name="listeUsers", options={"expose"=true})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listeUsersAction(Request $request)
    {
        $search = $request->query->get("recherche");
        if ($search == null) {
            $search = '%%';
        }

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $dql = "SELECT p FROM AppBundle:User p WHERE p.username like :search";

        $query = $em->createQuery($dql);
        $query->setParameters(array(
            'search' => '%' . $search . '%',
        ));

        $paginator = $this->get('knp_paginator');
        $users = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            20/*limit per page*/,
            array('defaultSortFieldName' => ['p.username'], 'defaultSortDirection' => 'asc')
        );

        return $this->render('user/listeUsers.html.twig', [
            'users' => $users
        ]);
    }
}

<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Arc;
use AppBundle\Entity\Medecin;
use AppBundle\Entity\User;
use AppBundle\Form\UserAdminType;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/arcalib")
 */
class UserController extends Controller
{
// ------------------------------------------Edit USER----------------------------------------------------- 

    /**
     * @Route("/utilisateur/editer/{id}", name="editUser")
     * @Route("/utilisateur/ajouter", name="addUser")
     * @param Request $request
     * @param null $id
     * @return RedirectResponse|Response
     */
    public function editUserAction(Request $request, $id=null)
    {
        $em = $this->getDoctrine()->getManager();
        if(!$id) {
            $user = new User();
            $form = $this->get('form.factory')->create(UserAdminType::class, $user,['validation_groups'=>['Registration']]);
        }
        else {
            $user = $em->getRepository(User::class)->find($id);
            if(!$user)
                $this->createNotFoundException('Utilisateur non trouvé');
            $form = $this->get('form.factory')->create(UserAdminType::class, $user,['validation_groups'=>['Profile']]);
        }

        if ($this->getUser() != $user)
            $this->denyAccessUnlessGranted('ROLE_ADMIN', $user, "Vous n'avez pas les droits pour cette action");

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $userManager = $this->get('fos_user.user_manager');

            $alreadyExist = false;
            if ($medecin = $user->getMedecin())
                $alreadyExist = $userManager->findUserBy(["medecin"=>$medecin]);

            if ($alreadyExist && $alreadyExist != $user) {
                $this->addFlash(
                    'danger',
                    'Il existe déjà un utilisateur pour ce médecin c\'est '. $alreadyExist->getUsername()
                );
                return $this->render('@FOSUser/Profile/edit.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            $alreadyExist = false;
            if ($arc = $user->getArc())
                $alreadyExist = $userManager->findUserBy(["arc"=>$arc]);

            if ($alreadyExist && $alreadyExist != $user) {
                $this->addFlash(
                    'danger',
                    'Il existe déjà un utilisateur pour cet arc c\'est '. $alreadyExist->getUsername()
                );
                return $this->render('@FOSUser/Profile/edit.html.twig', [
                    'form' => $form->createView(),
                ]);
            }

            if($form->get('addMedecin')->getData())
            {
                $medecin = new Medecin();
                $medecin->setNom($user->getNom());
                $medecin->setPrenom($user->getPrenom());
                $em->persist($medecin);
                $em->flush();
                $user->setMedecin($medecin);
            }

            if($form->get('addArc')->getData())
            {
                $arc = new Arc();
                $arc->setNomArc($user->getNom());
                $arc->setPrenomArc($user->getPrenom());
                $em->persist($arc);
                $em->flush();
                $user->setArc($arc);
            }

            $userManager->updateUser($user);
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
	 * @param User $user
	 * @return RedirectResponse
	 */
    public function deleteUserAction(User $user)
    {
        $em = $this->getDoctrine()->getManager();

        $em->remove($user);
        $em->flush();

        return $this->redirectToRoute("listeUsers");
    }

// ------------------------------------------Liste USER-----------------------------------------------------

    /**
     * @Route("/utilisateurs/", name="listeUsers", options={"expose"=true})
     * @param Request $request
     * @return Response
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

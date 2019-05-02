<?php

namespace AppBundle\Controller;

use AppBundle\Entity\LibCim10;
use AppBundle\Entity\Patient;
use AppBundle\Form\LibCim10Type;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/arcalib")
 */
class LibCim10Controller extends Controller
{
    // ------------------------------------------ADD CIM10-----------------------------------------------------  
    /**
     * @Route("/libcim10/ajouter", name="addLibCim10")
     * @Security("has_role('ROLE_ARC')")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function addLibCim10Action(Request $request)
    {
        $libCim10 = new LibCim10();

        $form = $this->get('form.factory')->create(LibCim10Type::class, $libCim10);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->persist($libCim10);
            $em->flush();

            return $this->redirectToRoute("listeLibCim10s");
        }

        return $this->render('libCim10/editLibCim10.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // ------------------------------------------EDIT CIM10-----------------------------------------------------  

	/**
	 * @Route("/libcim10/editer/{id}", name="editLibCim10")
	 * @Security("has_role('ROLE_ARC')")
	 * @param Request $request
	 * @param LibCim10 $libCim10
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
	 */
    public function editLibCim10Action(Request $request, LibCim10 $libCim10)
    {
        $em = $this->getDoctrine()->getManager();
        $emPatient = $em->getRepository(Patient::class);
        $patients = $emPatient->findBy(["libCim10" => $libCim10], ["nom" => "ASC"]);
        $patientsCount = count($patients);

        $form = $this->get('form.factory')->create(LibCim10Type::class, $libCim10);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->flush();

            return $this->redirectToRoute("listeLibCim10s");
        }

        return $this->render('libCim10/editLibCim10.html.twig', [
            'form' => $form->createView(),
            'patients' => $patients,
            'patientsCount' => $patientsCount
        ]);
    }

    // ------------------------------------------DEL CIM10-----------------------------------------------------  

	/**
	 * @Route("/libcim10/supprimer/{id}", name="deleteLibCim10", options={"expose"=true})
	 * @Security("has_role('ROLE_ARC')")
	 * @param LibCim10 $libCim10
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse
	 */
    public function deleteLibCim10Action(LibCim10 $libCim10)
    {
        $em = $this->getDoctrine()->getManager();

        $em->remove($libCim10);
        $em->flush();

        return $this->redirectToRoute("listeLibCim10s");
    }

    // ------------------------------------------Liste CIM10-----------------------------------------------------  

    /**
     * @Route("/libcim10s/", name="listeLibCim10s", options={"expose"=true})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listeLibcim10sAction(Request $request)
    {
        $search = $request->query->get("recherche");
        if ($search == null) {
            $search = '%%';
        }

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $dql = "SELECT p FROM AppBundle:LibCim10 p WHERE p.libCourt like :search or p.cim10code like :search or p.libLong like :search";

        $query = $em->createQuery($dql);
        $query->setParameters(array(
            'search' => '%' . $search . '%',
        ));

        $paginator = $this->get('knp_paginator');
        $libcim10s = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            20/*limit per page*/,
            array('defaultSortFieldName' => ['p.utile'], 'defaultSortDirection' => 'desc')
        );

        return $this->render('libCim10/listeLibCim10s.html.twig', [
            'libcim10s' => $libcim10s
        ]);
    }

	/**
	 * @Route("/libcim10/editer/utile/{id}", name="editUtile", options={"expose"=true})
	 * @Security("has_role('ROLE_ARC')")
	 * @param Request $request
	 * @param LibCim10 $libCim10
	 * @return JsonResponse
	 */
    public function editUtileAction(Request $request, LibCim10 $libCim10)
    {
        $em = $this->getDoctrine()->getManager();

        $libCim10->setUtile(($request->get("checked") == "true") ? true : false);
        $em->flush();

        return new JsonResponse(true);
    }
}

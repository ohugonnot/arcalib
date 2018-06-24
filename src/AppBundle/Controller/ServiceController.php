<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Service;
use AppBundle\Form\ServiceType;
use AppBundle\Services\CsvToArray;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;


/**
 * @Route("/arcalib")
 */
class ServiceController extends Controller
{

    // ------------------------------------------ADD SERVICE----------------------------------------------------- 
    /**
     * @Route("/service/ajouter", name="addService")
     * @Security("has_role('ROLE_ARC')")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function addServiceAction(Request $request)
    {
        $service = new Service();

        $form = $this->get('form.factory')->create(ServiceType::class, $service);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($service);
            $em->flush();

            return $this->redirectToRoute("listeServices");
        }

        return $this->render('service/editService.html.twig', [
            'form' => $form->createView(),
        ]);
    }

// ------------------------------------------Edit SERVICE----------------------------------------------------- 

    /**
     * @Route("/service/editer/{id}", name="editService")
     * @Security("has_role('ROLE_ARC')")
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editServiceAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $emService = $em->getRepository(Service::class);
        $service = $emService->find($id);

        $form = $this->get('form.factory')->create(ServiceType::class, $service);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {

            $em->flush();

            return $this->redirectToRoute("listeServices");
        }

        return $this->render('service/editService.html.twig', [
            'form' => $form->createView(),
        ]);
    }

// ------------------------------------------Supp SERVICE-----------------------------------------------------

    /**
     * @Route("/service/supprimer/{id}", name="deleteService", options={"expose"=true})
     * @Security("has_role('ROLE_ARC')")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteServiceAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $emService = $em->getRepository(Service::class);
        $service = $emService->find($id);

        $em->remove($service);
        $em->flush();

        return $this->redirectToRoute("listeServices");
    }

// ------------------------------------------Liste SERVICE-----------------------------------------------------

    /**
     * @Route("/services/", name="listeServices", options={"expose"=true})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listeServicesAction(Request $request)
    {
        $search = $request->query->get("recherche");
        if ($search == null) {
            $search = '%%';
        }

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $dql = "SELECT p FROM AppBundle:Service p WHERE p.nom like :search";

        $query = $em->createQuery($dql);
        $query->setParameters(array(
            'search' => '%' . $search . '%',
        ));

        $paginator = $this->get('knp_paginator');
        $services = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            20/*limit per page*/,
            array('defaultSortFieldName' => ['p.nom'], 'defaultSortDirection' => 'asc')
        );

        return $this->render('service/listeServices.html.twig', [
            'services' => $services
        ]);
    }

    /**
     * @param CsvToArray $csvToArray
     * @param bool $checkIfExist
     * @param bool $truncate
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Doctrine\Common\Persistence\Mapping\MappingException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function importAction(CsvToArray $csvToArray, $checkIfExist = true, $truncate = true)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $emService = $em->getRepository(Service::class);

        if ($truncate) {
            $em->createQuery('DELETE AppBundle:Service s')->execute();
        }

        $file = $this->get('kernel')->getRootDir() . '/../bdd/service.csv';
        $services = $csvToArray->convert($file, ";");

        $bulkSize = 500;
        $i = 0;
        foreach ($services as $s) {
            $i++;
            $service = false;

            if (empty($s["SERVICE"])) {
                continue;
            }

            foreach ($s as $k => $v) {
                $s[$k] = trim($v);
            }

            if ($checkIfExist) {
                $exist = $emService->findOneBy(["nom" => $s["SERVICE"]]);
                if ($exist) {
                    $service = $exist;
                }
            }

            if (!$service) {
                $service = new Service();
            }

            $service->setNom($s["SERVICE"]);
            $em->persist($service);

            if ($i % $bulkSize == 0) {
                $em->flush();
                $em->clear();
            }
        }

        $em->flush();
        $em->clear();

        return $this->redirectToRoute("listeServices");
    }

// ----------------------------------------------------------------------------------------------
}

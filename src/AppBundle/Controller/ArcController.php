<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Arc;
use AppBundle\Entity\Service;
use AppBundle\Form\ArcType;
use AppBundle\Services\CsvToArray;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/arcalib")
 */
class ArcController extends Controller
{
// ------------------------------------------ADD ARC-----------------------------------------------------  
    /**
     * @Route("/arc/ajouter", name="addArc")
     * @Security("has_role('ROLE_ADMIN')")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function addArcAction(Request $request)
    {
        $arc = new Arc();
        $form = $this->get('form.factory')->create(ArcType::class, $arc);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($arc);
            $em->flush();

            return $this->redirectToRoute("listeArcs");
        }

        return $this->render('arc/editArc.html.twig', [
            'form' => $form->createView(),
        ]);
    }

// ------------------------------------------EDIT ARC-----------------------------------------------------

    /**
     * @Route("/arc/editer/{id}", name="editArc")
     * @Security("has_role('ROLE_ADMIN')")
     * @param Request $request
     * @param Arc $arc
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editArcAction(Request $request, Arc $arc)
    {
        $form = $this->get('form.factory')->create(ArcType::class, $arc);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->flush();

            return $this->redirectToRoute("listeArcs");
        }

        return $this->render('arc/editArc.html.twig', [
            'form' => $form->createView(),
        ]);
    }

// ------------------------------------------SUPP ARC-----------------------------------------------------

    /**
     * @param Arc $arc
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function deleteArcAction(Arc $arc)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $em->remove($arc);
        $em->flush();

        return $this->redirectToRoute("listeArcs");
    }

    // ------------------------------------------LISTE ARC-----------------------------------------------------

    /**
     * @Route("/arcs/", name="listeArcs", options={"expose"=true})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listeArcsAction(Request $request)
    {
        $search = $request->query->get("recherche");
        if ($search == null) {
            $search = '%%';
        }

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $emArc = $em->getRepository(Arc::class);

        $query = $emArc->getQuery($search);

        $paginator = $this->get('knp_paginator');
        $arcs = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            20/*limit per page*/,
            array('defaultSortFieldName' => ['a.nomArc'], 'defaultSortDirection' => 'asc')
        );

        return $this->render('arc/listeArcs.html.twig', [
            'arcs' => $arcs
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
        $emArc = $em->getRepository(Arc::class);

        if ($truncate) {
            $em->createQuery('DELETE AppBundle:Arc a')->execute();
        }

        $file = $this->get('kernel')->getRootDir() . '/../bdd/arc.csv';
        $arcs = $csvToArray->convert($file, ";");

        $bulkSize = 500;
        $i = 0;
        foreach ($arcs as $a) {
            $i++;
            $arc = false;

            foreach ($a as $k => $v) {
                $a[$k] = trim($v);
            }

            if ($checkIfExist) {
                $exist = $emArc->findOneBy(["nomArc" => $a["Nom ARC"]]);
                if ($exist) {
                    $arc = $exist;
                }
            }

            if (!$arc) {
                $arc = new Arc();
            }

            $datIn = \DateTime::createFromFormat('d/m/Y', $a["Date d'entrée"]);
            $datOut = \DateTime::createFromFormat('d/m/Y', $a["Date de sortie"]);

            if (!$datIn) {
                $datIn = null;
            }

            if (!$datOut) {
                $datOut = null;
            }

            $arc->setNomArc($a["Nom ARC"]);
            $arc->setDatIn($datIn);
            $arc->setDatOut($datOut);
            $arc->setIniArc($a["Initiales"]);
            $arc->setDect($a["n° Poste"]);
            $arc->setTel($a["Teléphone"]);
            $arc->setMail($a["Mail"]);

            if ($service = $em->getRepository(Service::class)->findOneBy(["nom" => $a["SERVICE"]])) {
                $arc->setService($service);
            }

            $em->persist($arc);

            if ($i % $bulkSize == 0) {
                $em->flush();
                $em->clear();
            }
        }

        $em->flush();
        $em->clear();

        return $this->redirectToRoute("listeArcs");
    }
}

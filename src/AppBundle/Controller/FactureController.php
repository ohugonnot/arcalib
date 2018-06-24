<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Facture;
use AppBundle\Form\FactureType;
use AppBundle\Services\CsvToArray;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @Route("/arcalib")
 * @Security("has_role('ROLE_ARC')")
 */
class FactureController extends Controller
{

    // ------------------------------------------ADD FACTURE-----------------------------------------------------
    /**
     * @Route("/facture/ajouter", name="addFacture")
     * @Security("has_role('ROLE_ARC')")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function addFactureAction(Request $request)
    {
        $facture = new Facture();

        $form = $this->get('form.factory')->create(FactureType::class, $facture);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {

            $em = $this->getDoctrine()->getManager();
            $em->persist($facture);
            $em->flush();

            return $this->redirectToRoute("listeFactures");
        }

        return $this->render('facture/editFacture.html.twig', [
            'form' => $form->createView(),
        ]);
    }

// ------------------------------------------Edit FACTURE-----------------------------------------------------

    /**
     * @Route("/facture/editer/{id}", name="editFacture")
     * @Security("has_role('ROLE_ARC')")
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editFactureAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $emFacture = $em->getRepository(Facture::class);
        $facture = $emFacture->find($id);

        $facturesSoeurs = $emFacture->findBy(['essai' => $facture->getEssai()], ["date" => "DESC"]);

        $form = $this->get('form.factory')->create(FactureType::class, $facture);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {

            $em->flush();
            return $this->redirectToRoute("listeFactures");
        }

        return $this->render('facture/editFacture.html.twig', [
            'form' => $form->createView(),
            'facturesSoeurs' => $facturesSoeurs
        ]);
    }

// ------------------------------------------delete FACTURE-----------------------------------------------------

    /**
     * @Route("/facture/supprimer/{id}", name="deleteFacture", options={"expose"=true})
     * @Security("has_role('ROLE_ADMIN')")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteFactureAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $emFacture = $em->getRepository(Facture::class);
        $facture = $emFacture->find($id);

        $em->remove($facture);
        $em->flush();

        return $this->redirectToRoute("listeFactures");
    }


// ------------------------------------------Liste FACTURE-----------------------------------------------------

    /**
     * @Route("/factures/", name="listeFactures", options={"expose"=true})
     * @Security("has_role('ROLE_ARC')")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listeFacturesAction(Request $request)
    {
        $search = $request->query->get("recherche");
        if ($search == null) {
            $search = '%%';
        }


        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $emFacture = $em->getRepository(Facture::class);
        $query = $emFacture->getQuery($user, $search);

        $paginator = $this->get('knp_paginator');
        $factures = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            20/*limit per page*/,
            array('defaultSortFieldName' => ['f.numero'], 'defaultSortDirection' => 'asc')
        );

        return $this->render('facture/listeFactures.html.twig', [
            'factures' => $factures
        ]);
    }

    /**
     * @Route("/factures/export", name="exportFactures", options={"expose"=true})
     * @Security("has_role('ROLE_ARC')")
     * @param CsvToArray $export
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function exportFacturesAction(CsvToArray $export)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $emFacture = $em->getRepository(Facture::class);
        $factures = $emFacture->findAll();

        return $export->exportCSV($factures, "factures");
    }


    /**
     * @Route("/facture/upload/pdf/{id}", name="uploadFacturePDF", options={"expose"=true})
     * @Security("has_role('ROLE_ARC')")
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function uploadFacturePDFAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $emFacture = $em->getRepository(Facture::class);
        $facture = $emFacture->find($id);

        $file = $request->files->get('file');
        $path = $this->get('kernel')->getRootDir() . '/Resources/' . $this->getParameter("pdf_directory_asset");
        $fileName = date('m-d-Y_his') . '-' . $file->getClientOriginalName();

        $file->move(
            $path,
            $fileName
        );

        if ($facture->getFacture() != null) {
            $file_path = $path . '/' . $facture->getFacture();
            if (file_exists($file_path)) unlink($file_path);
        }

        $facture->setFacture($fileName);
        $em->flush();

        $file_path = $this->generateUrl('downloadPDF', array('pdf' => $facture->getFacture()), UrlGeneratorInterface::ABSOLUTE_URL);

        return new JsonResponse(["success" => true, "fileName" => $file_path]);
    }

    /**
     * @Route("/facture/remove/pdf/{id}", name="removeFacturePDF", options={"expose"=true})
     * @Security("has_role('ROLE_ARC')")
     * @param $id
     * @return JsonResponse
     */
    public function removeFacturePDFAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $emFacture = $em->getRepository(Facture::class);
        $facture = $emFacture->find($id);

        $file_path = $this->get('kernel')->getRootDir() . '/Resources/' . $this->getParameter("pdf_directory_asset") . '/' . $facture->getFacture();
        if (file_exists($file_path)) unlink($file_path);

        $facture->setFacture(null);
        $em->flush();

        return new JsonResponse(["success" => true]);
    }

    /**
     * @Route("/facture/get/pdf/{id}", name="getFacturePDF", options={"expose"=true})
     * @param $id
     * @return JsonResponse
     */
    public function getFacturePDFAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $emFacture = $em->getRepository(Facture::class);
        $facture = $emFacture->find($id);

        if ($facture->getFacture()) {
            $file_path = $this->generateUrl('downloadPDF', array('pdf' => $facture->getFacture()), UrlGeneratorInterface::ABSOLUTE_URL);
        } else {
            $file_path = null;
        }

        return new JsonResponse(["facture" => $file_path]);
    }

    /**
     * @Route("/pdf/factures/{pdf}", name="downloadPDF", options={"expose"=true})
     * @Security("has_role('ROLE_ARC')")
     * @param $pdf
     * @return BinaryFileResponse
     */
    public function downloadPDFAction($pdf)
    {
        $path = $this->get('kernel')->getRootDir() . '/Resources/' . $this->getParameter("pdf_directory_asset");
        if (file_exists($path) . '/' . $pdf) {
            $file_path = $path . '/' . $pdf;
        } else {
            throw $this->createNotFoundException('Le pdf n\'existe pas.');
        }

        return new BinaryFileResponse($file_path);
    }
}

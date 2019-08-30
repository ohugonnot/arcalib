<?php

namespace AppBundle\Controller;

use AppBundle\Entity\DocumentEssai;
use AppBundle\Entity\Essais;
use AppBundle\Form\DocumentEssaiType;
use Doctrine\Common\Collections\ArrayCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @Route("/arcalib")
 */
class DocumentEssaiController extends Controller
{

    // ------------------------------------------ADD Document-----------------------------------------------------
	/**
	 * @Route("/documentEssais/essai/{id}/ajouter", name="addDocumentEssai", options={"expose"=true})
	 * @Security("has_role('ROLE_ARC')")
	 * @param Request $request
	 * @param Essais $essai
	 * @return RedirectResponse|Response|NotFoundHttpException
	 */
    public function addDocumentEssaiAction(Request $request, Essais $essai)
    {
        $em = $this->getDoctrine()->getManager();
        $documentEssai = new DocumentEssai();
        $documentEssai->setEssai($essai);

        $form = $this->get('form.factory')->create(DocumentEssaiType::class, $documentEssai);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {

            $em->persist($documentEssai);
            $em->flush();

            return $this->redirectToRoute("editDocumentEssai", ["id" => $documentEssai->getId()]);
        }

        $emDocumentEssai = $em->getRepository(DocumentEssai::class);
        $allDocumentEssais = new ArrayCollection($emDocumentEssai->findBy(["essai" => $essai], ["date" => "DESC"]));

        return $this->render('documentEssai/editDocumentEssai.html.twig', [
            'form' => $form->createView(),
            'allDocumentEssais' => $allDocumentEssais
        ]);
    }

	/**
	 * @Route("/documentEssais/editer/{id}", name="editDocumentEssai", options={"expose"=true})
	 * @param Request $request
	 * @param DocumentEssai $documentEssai
	 * @return RedirectResponse|Response
	 */
    public function editDocumentEssaiAction(Request $request, DocumentEssai $documentEssai)
    {
        $em = $this->getDoctrine()->getManager();
        $emDocumentEssai = $em->getRepository(DocumentEssai::class);

        /** @var Essais $essai */
        $essai = $documentEssai->getEssai();

        $form = $this->get('form.factory')->create(DocumentEssaiType::class, $documentEssai);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {

            if (!$this->get('security.authorization_checker')->isGranted('ROLE_ARC')) {
                throw $this->createAccessDeniedException('Vous n\'avez pas le droit de sauvegarder !');
            }
            $em->flush();

            return $this->redirectToRoute("listeDocumentEssais", ["id" => $essai->getId()]);
        }

        $allDocumentEssais = new ArrayCollection($emDocumentEssai->findBy(["essai" => $essai], ["date" => "DESC"]));
        if ($allDocumentEssais->contains($documentEssai)) {
            $index = $allDocumentEssais->indexOf($documentEssai);
            $prev = $allDocumentEssais->get($index - 1);
            $next = $allDocumentEssais->get($index + 1);
        }

        return $this->render('documentEssai/editDocumentEssai.html.twig', [
            'form' => $form->createView(),
            'url' => $this->getUrlDocument($documentEssai),
            'prev' => $prev ?? null,
            'next' => $next ?? null,
            'count' => $allDocumentEssais->count(),
            'index' => $index ?? null,
            'allDocumentEssais' => $allDocumentEssais,
        ]);
    }

    /**
     * @param DocumentEssai $documentEssai
     * @return null|string
     */
    private function getUrlDocument(DocumentEssai $documentEssai)
    {
        $pdf = $this->getDoctrine()->getManager()->getRepository(DocumentEssai::class)->findPDF($this->getUser(), $documentEssai->getFile());
        if ($pdf) {
            $file_path = $this->generateUrl('downloadDocumentEssaiPDF', array('pdf' => $documentEssai->getFile()), UrlGeneratorInterface::ABSOLUTE_URL);
        } else {
            $file_path = null;
        }

        return $file_path;
    }

    // ------------------------------------------delete DOCUMENT-----------------------------------------------------

	/**
	 * @Route("/documentEssais/essai/{id}/voir", name="voirDocumentEssai", options={"expose"=true})
	 * @param Essais $essai
	 * @return Response|RedirectResponse
	 */
    public function firstDocumentEssaiAction(Essais $essai)
    {
        $em = $this->getDoctrine()->getManager();
        $emDocumentEssai = $em->getRepository(DocumentEssai::class);

        $allDocumentEssais = new ArrayCollection($emDocumentEssai->findBy(["essai" => $essai], ["date" => "DESC"]));

        if (!$allDocumentEssais->isEmpty()) {
            return $this->redirectToRoute('editDocumentEssai', ["id" => $allDocumentEssais->first()->getId()], 301);
        }

        return $this->forward("AppBundle:DocumentEssai:listeDocumentEssaisInclusion", [
            "id" => $essai->getId()
        ]);
    }

	/**
	 * @Route("/documentEssais/supprimer/{id}", name="deleteDocumentEssai", options={"expose"=true})
	 * @Security("has_role('ROLE_ADMIN')")
	 * @param DocumentEssai $documentEssai
	 * @return RedirectResponse
	 */
    public function deleteDocumentEssaiAction(DocumentEssai $documentEssai)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var Essais $essai */
        $essai = $documentEssai->getEssai();

        $em->remove($documentEssai);
        $em->flush();

        return $this->redirectToRoute("listeDocumentEssais", ["id" => $essai->getId()]);
    }

	/**
	 * @Route("/documentEssais/inclusion/{id}", name="listeDocumentEssais", options={"expose"=true})
	 * @param Request $request
	 * @param Essais $essai
	 * @return Response
	 */
    public function listeDocumentEssaisInclusionAction(Request $request, Essais $essai)
    {
        $search = $request->query->get("recherche");
        if ($search == null) {
            $search = '%%';
        }

        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $emDocumentEssai = $em->getRepository(DocumentEssai::class);

        $query = $emDocumentEssai->getQuery($user, $search, $essai->getId());

        $paginator = $this->get('knp_paginator');
        $documentEssais = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            20/*limit per page*/,
            array('defaultSortFieldName' => ['d.date'], 'defaultSortDirection' => 'desc')
        );

        return $this->render('documentEssai/listeDocumentEssais.html.twig', [
            'documentEssais' => $documentEssais,
            'essai' => $essai,
            'pdf_document_directory_asset' => $this->getParameter('pdf_document_directory_asset')
        ]);
    }

	/**
     * TODO : code duplicate content gestion file
	 * @Route("/documentEssais/upload/pdf/{id}", name="uploadDocumentEssaiPDF", options={"expose"=true})
	 * @Security("has_role('ROLE_ARC')")
	 * @param Request $request
	 * @param DocumentEssai $documentEssai
	 * @return JsonResponse
	 */
    public function uploadDocumentEssaiPDFAction(Request $request, DocumentEssai $documentEssai)
    {
        $em = $this->getDoctrine()->getManager();

	    /** @var Essais $essai */
        $essai = $documentEssai->getEssai();

        $file = $request->files->get('file');
        $fileName = date('m-d-Y_his') . '-' . $file->getClientOriginalName();
        $path = $this->getBasePath() . $essai->getId();

        $file->move(
            $path,
            $fileName
        );

        if ($documentEssai->getFile() != null) {
            $file_path = $path . '/' . $documentEssai->getFile();
            if (file_exists($file_path)) unlink($file_path);
        }

        $documentEssai->setFile($fileName);
        $em->flush();

        return new JsonResponse(["success" => true, "fileName" => $this->getUrlDocument($documentEssai)]);
    }

	/**
	 * @Route("/documentEssais/remove/pdf/{id}", name="removeDocumentEssaiPDF", options={"expose"=true})
	 * @Security("has_role('ROLE_ARC')")
	 * @param DocumentEssai $documentEssai
	 * @return JsonResponse
	 */
    public function removeDocumentEssaiPDFAction(DocumentEssai $documentEssai)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var Essais $essai */
        $essai = $documentEssai->getEssai();

        $path = $this->getBasePath() . $essai->getId();

        $file_path = $path . '/' . $documentEssai->getFile();
        if (file_exists($file_path) && $documentEssai->getFile()) unlink($file_path);

        $documentEssai->setFile(null);
        $em->flush();

        return new JsonResponse(["success" => true]);
    }

	/**
	 * @Route("/documentEssai/get/pdf/{id}", name="getDocumentEssaiPDF", options={"expose"=true})
	 * @param DocumentEssai $documentEssai
	 * @return JsonResponse
	 */
    public function getDocumentEssaiPDFAction(DocumentEssai $documentEssai)
    {

        return new JsonResponse(["document" => $this->getUrlDocument($documentEssai)]);
    }

    /**
     * @Route("/pdf/documentEssai/{pdf}", name="downloadDocumentEssaiPDF", options={"expose"=true})
     * @param $pdf
     * @return BinaryFileResponse
     */
    public function downloadDocumentEssaiPDFAction($pdf)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $documentEssai = $em->getRepository(DocumentEssai::class)->findPDF($user, $pdf);

        if (!$documentEssai) {
            throw $this->createNotFoundException("Pas de document trouvé avec le pdf : $pdf");
        }
        /** @var Essais $essai */
        $essai = $documentEssai->getEssai();

        $path = $this->getBasePath() . $essai->getId();
        if (file_exists($path) . '/' . $pdf) {
            $file_path = $path . '/' . $pdf;
        } else {
            throw $this->createNotFoundException('Le fichier pdf n\'à pas été trouvé sur le disque.');
        }

        return new BinaryFileResponse($file_path);
    }

    private function getBasePath()
    {
        return $this->get('kernel')->getRootDir() . '/Resources/' . $this->getParameter('pdf_document_directory_asset') . '/essai/';
    }
}
<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Document;
use AppBundle\Entity\Inclusion;
use AppBundle\Form\DocumentType;
use Doctrine\Common\Collections\ArrayCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

/**
 * @Route("/arcalib")
 */
class DocumentController extends Controller
{

    // ------------------------------------------ADD Document-----------------------------------------------------
	/**
	 * @Route("/documents/inclusion/{id}/ajouter", name="addDocument", options={"expose"=true})
	 * @Security("has_role('ROLE_ARC')")
	 * @param Request $request
	 * @param Inclusion $inclusion
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response|\Symfony\Component\HttpKernel\Exception\NotFoundHttpException
	 */
    public function addDocumentAction(Request $request, Inclusion $inclusion)
    {
        $em = $this->getDoctrine()->getManager();
        $document = new Document();
        $document->setInclusion($inclusion);

        $form = $this->get('form.factory')->create(DocumentType::class, $document);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {

            $em->persist($document);
            $em->flush();

            return $this->redirectToRoute("editDocument", ["id" => $document->getId()]);
        }

        $emDocument = $em->getRepository(Document::class);
        $allDocuments = new ArrayCollection($emDocument->findBy(["inclusion" => $inclusion], ["date" => "DESC"]));

        return $this->render('document/editDocument.html.twig', [
            'form' => $form->createView(),
            'allDocuments' => $allDocuments
        ]);
    }

	/**
	 * @Route("/documents/editer/{id}", name="editDocument", options={"expose"=true})
	 * @param Request $request
	 * @param Document $document
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
	 */
    public function editDocumentAction(Request $request, Document $document)
    {
        $em = $this->getDoctrine()->getManager();
        $emDocument = $em->getRepository(Document::class);

        /** @var Inclusion $inclusion */
        $inclusion = $document->getInclusion();

        $form = $this->get('form.factory')->create(DocumentType::class, $document);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {

            if (!$this->get('security.authorization_checker')->isGranted('ROLE_ARC')) {
                throw $this->createAccessDeniedException('Vous n\'avez pas le droit de sauvegarder !');
            }
            $em->flush();

            return $this->redirectToRoute("inclusion_list_documents", ["id" => $inclusion->getId()]);
        }

        $allDocuments = new ArrayCollection($emDocument->findBy(["inclusion" => $inclusion], ["date" => "DESC"]));
        if ($allDocuments->contains($document)) {
            $index = $allDocuments->indexOf($document);
            $prev = $allDocuments->get($index - 1);
            $next = $allDocuments->get($index + 1);
        }

        return $this->render('document/editDocument.html.twig', [
            'form' => $form->createView(),
            'url' => $this->getUrlDocument($document),
            'prev' => $prev ?? null,
            'next' => $next ?? null,
            'count' => $allDocuments->count(),
            'index' => $index ?? null,
            'allDocuments' => $allDocuments,
        ]);
    }

    /**
     * @param Document $document
     * @return null|string
     */
    private function getUrlDocument(Document $document)
    {
        $pdf = $this->getDoctrine()->getManager()->getRepository(Document::class)->findPDF($this->getUser(), $document->getFile());
        if ($pdf) {
            $file_path = $this->generateUrl('downloadDocumentPDF', array('pdf' => $document->getFile()), UrlGeneratorInterface::ABSOLUTE_URL);
        } else {
            $file_path = null;
        }

        return $file_path;
    }

    // ------------------------------------------delete DOCUMENT-----------------------------------------------------

	/**
	 * @Route("/documents/inclusion/{id}/voir", name="voirDocument", options={"expose"=true})
	 * @param Inclusion $inclusion
	 * @return \Symfony\Component\HttpFoundation\Response|RedirectResponse
	 */
    public function firstDocumentAction(Inclusion $inclusion)
    {
	    $em = $this->getDoctrine()->getManager();
        $emDocument = $em->getRepository(Document::class);

        $allDocuments = new ArrayCollection($emDocument->findBy(["inclusion" => $inclusion], ["date" => "DESC"]));

        if (!$allDocuments->isEmpty()) {
            return $this->redirectToRoute('editDocument', ["id" => $allDocuments->first()->getId()], 301);
        }

        return $this->forward("AppBundle:Document:listeDocumentsInclusion", [
            "id" => $inclusion->getId()
        ]);
    }

	/**
	 * @Route("/documents/supprimer/{id}", name="deleteDocument", options={"expose"=true})
	 * @Security("has_role('ROLE_ADMIN')")
	 * @param Document $document
	 * @return \Symfony\Component\HttpFoundation\RedirectResponse
	 */
    public function deleteDocumentAction(Document $document)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var Inclusion $inclusion */
        $inclusion = $document->getInclusion();

        $em->remove($document);
        $em->flush();

        return $this->redirectToRoute("inclusion_list_documents", ["id" => $inclusion->getId()]);
    }

	/**
	 * @Route("/documents/inclusion/{id}", name="inclusion_list_documents", options={"expose"=true})
	 * @param Request $request
	 * @param Inclusion $inclusion
	 * @return \Symfony\Component\HttpFoundation\Response
	 */
    public function listeDocumentsInclusionAction(Request $request, Inclusion $inclusion)
    {
        $search = $request->query->get("recherche");
        if ($search == null) {
            $search = '%%';
        }

        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $emDocument = $em->getRepository(Document::class);

        $query = $emDocument->getQuery($user, $search, $inclusion->getId());

        $paginator = $this->get('knp_paginator');
        $documents = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            20/*limit per page*/,
            array('defaultSortFieldName' => ['d.date'], 'defaultSortDirection' => 'desc')
        );

        return $this->render('document/listeDocuments.html.twig', [
            'documents' => $documents,
            'inclusion' => $inclusion,
            'pdf_document_directory_asset' => $this->getParameter('pdf_document_directory_asset')
        ]);
    }

	/**
	 * @Route("/documents/upload/pdf/{id}", name="uploadDocumentPDF", options={"expose"=true})
	 * @Security("has_role('ROLE_ARC')")
	 * @param Request $request
	 * @param Document $document
	 * @return JsonResponse
	 */
    public function uploadDocumentPDFAction(Request $request, Document $document)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var Inclusion $inclusion */
        $inclusion = $document->getInclusion();

        $file = $request->files->get('file');
        $fileName = date('m-d-Y_his') . '-' . $file->getClientOriginalName();
        $path = $this->getBasePath() . $inclusion->getId();

        $file->move(
            $path,
            $fileName
        );

        if ($document->getFile() != null) {
            $file_path = $path . '/' . $document->getFile();
            if (file_exists($file_path)) unlink($file_path);
        }

        $document->setFile($fileName);
        $em->flush();

        return new JsonResponse(["success" => true, "fileName" => $this->getUrlDocument($document)]);
    }

	/**
	 * @Route("/documents/remove/pdf/{id}", name="removeDocumentPDF", options={"expose"=true})
	 * @Security("has_role('ROLE_ARC')")
	 * @param Document $document
	 * @return JsonResponse
	 */
    public function removeDocumentPDFAction(Document $document)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var Inclusion $inclusion */
        $inclusion = $document->getInclusion();
        $path = $this->getBasePath() . $inclusion->getId();
        $file_path = $path . '/' . $document->getFile();

        if (file_exists($file_path) && $document->getFile()) unlink($file_path);

        $document->setFile(null);
        $em->flush();

        return new JsonResponse(["success" => true]);
    }

	/**
	 * @Route("/document/get/pdf/{id}", name="getDocumentPDF", options={"expose"=true})
	 * @param Document $document
	 * @return JsonResponse
	 */
    public function getDocumentPDFAction(Document $document)
    {

        return new JsonResponse(["document" => $this->getUrlDocument($document)]);
    }

    /**
     * @Route("/pdf/document/{pdf}", name="downloadDocumentPDF", options={"expose"=true})
     * @param $pdf
     * @return BinaryFileResponse
     */
    public function downloadDocumentPDFAction($pdf)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $document = $em->getRepository(Document::class)->findPDF($user, $pdf);

        if (!$document) {
            throw $this->createNotFoundException("Pas de document trouvé avec le pdf : $pdf");
        }
        /** @var Inclusion $inclusion */
        $inclusion = $document->getInclusion();

        $path = $this->getBasePath() . $inclusion->getId();
        if (file_exists($path) . '/' . $pdf) {
            $file_path = $path . '/' . $pdf;
        } else {
            throw $this->createNotFoundException('Le fichier pdf n\'à pas été trouvé sur le disque.');
        }

        return new BinaryFileResponse($file_path);
    }

    private function getBasePath()
    {
        return $this->get('kernel')->getRootDir() . '/Resources/' . $this->getParameter('pdf_document_directory_asset') . '/inclusion/';
    }
}
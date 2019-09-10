<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Arc;
use AppBundle\Entity\Essais;
use AppBundle\Entity\Medecin;
use AppBundle\Entity\Service;
use AppBundle\Entity\Tag;
use AppBundle\Factory\EssaiFactory;
use AppBundle\Factory\FilFactorty;
use AppBundle\Services\CsvToArray;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * @Route("/arcalib")
 */
class EssaisController extends Controller
{
    /**
     * @Route("/essai/supprimer/{id}", name="deleteEssai", options={"expose"=true})
     * @Security("has_role('ROLE_ARC')")
     * @param Essais $essai
     * @return JsonResponse
     */
    public function deleteEssaiAction(Essais $essai)
    {
        $em = $this->getDoctrine()->getManager();
        $em->remove($essai);
        $em->flush();

        return new JsonResponse(true);
    }


    /**
     * @Route("/protocole/editer/{id}", name="editEssai", options={"expose"=true})
     * @Route("/protocole", name="protocole", options={"expose"=true})
     * @param Request $request
     * @param null $id
     * @return Response
     */
    public function protocoleAction(Request $request, $id = null)
    {
        $em = $this->getDoctrine()->getManager();
        $emEssai = $em->getRepository(Essais::class);

        if ($id) {
            $essai = $emEssai->find($id);
            return $this->redirectToRoute("protocole", ["id" => $id, "archive" => in_array($essai->getStatut(),[Essais::ARCHIVE, Essais::REFUS])]);
        }

        $user = $this->getUser();
        if ($request->get("archive") == true) {
            $essais = $emEssai->findArchived($user);
        } else {
            $essais = $emEssai->findNotArchived($user);
        }

        usort($essais, array($this, 'orderByStatut'));

        $medecins = $this->getDoctrine()->getManager()->getRepository(Medecin::class)->findBy([], ["nom" => "asc"]);
        $arcs = $this->getDoctrine()->getManager()->getRepository(Arc::class)->findBy([], ["nomArc" => "asc"]);
        $services = $this->getDoctrine()->getManager()->getRepository(Service::class)->findBy([], ["nom" => "asc"]);

        return $this->render('protocole/protocole.html.twig', [
            'essais' => $essais,
            "arcs" => $arcs,
            "medecins" => $medecins,
            'services' => $services,
        ]);
    }

    /**
     * TODO : duplicate code content search
     * @Route("/essais/", name="listeEssais", options={"expose"=true})
     * @param Request $request
     * @return Response
     */
    public function listeEssaisAction(Request $request)
    {
        $search = $request->query->get("recherche");
        if ($search == null) {
            $search = '%%';
        }

        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $emEssai = $em->getRepository(Essais::class);
        $query = $emEssai->getQuery($user, $search);

        $paginator = $this->get('knp_paginator');
        $essais = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            20/*limit per page*/,
            array('defaultSortFieldName' => ['e.nom'], 'defaultSortDirection' => 'asc')
        );

        return $this->render('essai/listeEssais.html.twig', [
            'essais' => $essais
        ]);
    }

    /**
     * @Route("/tag/search/", name="searchTag" , options={"expose"=true})
     * @param Request $request
     * @return JsonResponse
     */
    public function searchTagAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $emTag = $em->getRepository(Tag::class);
        /** @var Tag[] $tags */
        $tags = $emTag->searchTag($request->query->get("query"));

        $suggestions = [];
        foreach ($tags as $tag) {
            $suggestions[] = ['id' => $tag->getId(), 'nom' => $tag->getNom()];
        }

        return new JsonResponse($suggestions);
    }

    /**
     * @Route("/essai/select/{id}", name="selectProtocole", options={"expose"=true})
     * @param $id
     * @return JsonResponse
     */
    public function selectProtocoleAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        $emEssai = $em->getRepository(Essais::class);
        $essai = $emEssai->findArray($id, $user);
        return new JsonResponse($essai);
    }

    /**
     * @Route("/essai/{id}/fils/save", name="saveFil", options={"expose"=true})
     * @param FilFactorty $filFactorty
     * @param Request $request
     * @param $id
     * @return JsonResponse
     */
    public function saveFils(FilFactorty $filFactorty, Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        /** @var Essais $essai */
        $essai = $em->getRepository(Essais::class)->find($id);
        $fils = $request->request->get("appbundle_fils") ?? [];
        $ids = [];
        foreach ($fils as $k=>$fil)
        {
           $filEntity = $filFactorty->hydrate(null, $fil, $k);

           if (isset($filEntity->errorsMessage) && $filEntity->errorsMessage)
                return new JsonResponse(["success" => false, "message" => $filEntity->errorsMessage]);

           if (!$filEntity->getId()) {
               $em->persist($filEntity);
               $essai->addFil($filEntity);
               $em->flush();
           }
           $ids[] = $filEntity->getId();
        }
        foreach($essai->getFils() as $fil) {
            if(!in_array($fil->getId(),$ids))
                $em->remove($fil);
        }
        $em->flush();
        return new JsonResponse(["success" => true, 'ids' => $ids]);
    }

    /**
     * @Route("/essai/advanced/recherche/{query}", name="searchEssais", options={"expose"=true})
     * @param Request $request
     * @param null $query
     * @return JsonResponse
     */
    public function searchEssaisAction(Request $request, $query = null)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        $query = explode(" ", $query);
        $filters = $request->request->get("filters");
        $emEssai = $em->getRepository(Essais::class);
        $essais = $emEssai->findAdvancedArray($query, $filters, $user);

        return new JsonResponse($essais);
    }

    /**
     * @Route("/essai/recherche/{query}", name="rechercheEssai", options={"expose"=true})
     * @param $query
     * @return JsonResponse
     */
    public function rechercheEssaiAction($query)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        $query = explode(" ", $query);
        $emEssai = $em->getRepository(Essais::class);
        $essais = $emEssai->findByNomLike($query, $user);

        return new JsonResponse($essais);
    }

	/**
	 * @Route("/essai/save/{id}", name="saveEssai", options={"expose"=true})
	 * @Security("has_role('ROLE_ARC')")
	 * @param Request $request
	 * @param null $id
	 * @param EssaiFactory $essaiFactory
	 * @return JsonResponse
	 */
    public function saveEssaiAction(Request $request, EssaiFactory $essaiFactory, $id = null)
    {
        $em = $this->getDoctrine()->getManager();

        $essai = $em->getRepository(Essais::class)->find($id);
        if (!$essai) {
            $essai = new Essais();
            $em->persist($essai);
            $new = true;
        }

        $essai = $essaiFactory->hydrate($essai, $request->request->get("appbundle_essais"));

        if (isset($essai->errorsMessage) && $essai->errorsMessage)
            return new JsonResponse(["success" => false, "message" => $essai->errorsMessage]);

        if (isset($new) && $new)
            $em->persist($essai);
        $em->flush();

        return new JsonResponse(["success" => true, "protocole" => ["id" => $essai->getId()]]);
    }

    /**
     * @Route("/essais/export", name="exportEssais", options={"expose"=true})
     * @Security("has_role('ROLE_ARC')")
     * @param CsvToArray $export
     * @return StreamedResponse
     */
    public function exportEssaisAction(CsvToArray $export)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $emEssai = $em->getRepository(Essais::class);
        $essais = $emEssai->findAllByUser($user);

        return $export->exportCSV($essais, "essais");
    }

    /**
     * @Route("/essais/upload/pdf/{id}/{type}", name="uploadProtocolePDF", options={"expose"=true})
     * @Security("has_role('ROLE_ARC')")
     * @param Request $request
     * @param $type
     * @param Essais $essai
     * @return JsonResponse
     */
    public function uploadProtocolePDFAction(Request $request, $type, Essais $essai)
    {
        $em = $this->getDoctrine()->getManager();
        $file = $request->files->get('file');
        $fileName = date('m-d-Y_his') . '-' . $file->getClientOriginalName();
        $path = $this->get('kernel')->getRootDir() . '/Resources/' . $this->getParameter("pdf_directory_asset");

        $file->move(
            $path,
            $fileName
        );

        $setType = 'set' . ucfirst($type);
        $getType = 'get' . ucfirst($type);

        if ($essai->$getType() != null) {
            $file_path = $path . '/' . $essai->$getType();
            if (file_exists($file_path)) unlink($file_path);
        }

        $essai->$setType($fileName);
        $em->flush();

        return new JsonResponse(["success" => true, "fileName" => $fileName, "type" => $type]);
    }

    /**
     * @Route("/essais/remove/pdf/{id}/{type}", name="removeProtocolePDF", options={"expose"=true})
     * @Security("has_role('ROLE_ARC')")
     * @param $type
     * @param Essais $essai
     * @return JsonResponse
     */
    public function removeProtocolePDFAction($type, Essais $essai)
    {
        $em = $this->getDoctrine()->getManager();
        $path = $this->get('kernel')->getRootDir() . '/Resources/' . $this->getParameter("pdf_directory_asset");

        $getType = 'get' . ucfirst($type);
        $setType = 'set' . ucfirst($type);

        $file_path = $path . '/' . $essai->$getType();
        if (file_exists($file_path)) unlink($file_path);

        $essai->$setType(null);
        $em->flush();

        return new JsonResponse(["success" => true]);
    }

    /**
     * @Route("/pdf/essais/{pdf}", name="downloadProtocolePDF", options={"expose"=true})
     * @param $pdf
     * @return BinaryFileResponse|JsonResponse
     */
    public function downloadProtocolePDFAction($pdf)
    {
        if ($pdf == null) {
            return new JsonResponse(["path" => "/pdf/essais/"]);
        }

        $path = $this->get('kernel')->getRootDir() . '/Resources/' . $this->getParameter("pdf_directory_asset");
        if (file_exists($path) . '/' . $pdf) {
            $file_path = $this->get('kernel')->getRootDir() . '/Resources/' . $this->getParameter("pdf_directory_asset") . '/' . $pdf;
        } else {
            throw $this->createNotFoundException('Le pdf n\'existe pas.');
        }

        return new BinaryFileResponse($file_path);
    }

	/**
	 * @param Essais $a
	 * @param Essais $b
	 * @return bool|int
	 */
	private function orderByStatut(Essais $a, Essais $b)
    {
        $statuts = array_keys(Essais::STATUT);
        $statutA = array_search($a->getStatut(), $statuts);
        $statutB = array_search($b->getStatut(), $statuts);

        if ($statutA != $statutB) {
            return $statutA > $statutB;
        }

        return strcasecmp($a->getNom(), $b->getNom());
    }
}

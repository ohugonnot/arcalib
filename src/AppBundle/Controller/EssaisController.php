<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Arc;
use AppBundle\Entity\EssaiDetail;
use AppBundle\Entity\Essais;
use AppBundle\Entity\Medecin;
use AppBundle\Entity\Service;
use AppBundle\Entity\Tag;
use AppBundle\Form\EssaisType;
use AppBundle\Services\CsvToArray;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

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
     * @return \Symfony\Component\HttpFoundation\Response
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
     * @Route("/essais/", name="listeEssais", options={"expose"=true})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
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
     * @return JsonResponse
     */
    public function saveEssaiAction(Request $request, $id = null)
    {
        $em = $this->getDoctrine()->getManager();

        $essai = $em->getRepository(Essais::class)->find($id);
        if (!$essai) {
            $essai = new Essais();
            $new = true;
        }

        $form = $this->get('form.factory')->create(EssaisType::class, $essai);
        $form->handleRequest($request);
        $em->persist($essai);

        $params = $request->request->get("appbundle_essais");

        if (isset($params["detail"])) {
            $essaiDetail = $em->getRepository(EssaiDetail::class)->find($params["detail"]["id"]);

            if (!$essaiDetail) {
                $essaiDetail = new EssaiDetail();
            }

            $essaiDetail->setCrInc(($params["detail"]["crInc"] != '') ? $params["detail"]["crInc"] : null);
            $essaiDetail->setCrNonInc(($params["detail"]["crNonInc"] != '') ? $params["detail"]["crNonInc"] : null);
            $essaiDetail->setObjectif(($params["detail"]["objectif"] != '') ? $params["detail"]["objectif"] : null);
            $essaiDetail->setCalendar(($params["detail"]["calendar"] != '')?  $params["detail"]["calendar"] : null);
            $essai->setDetail($essaiDetail);
        }

        if (isset($params["tags"]) && is_string($params["tags"][0])) {
            $essai->clearTags();

            foreach ($params["tags"] as $tag) {

                if ($tag != '' and $tag != null) {
                    $tagExist = $em->getRepository(Tag::class)->findOneBy(["nom" => $tag]);

                    if (!$tagExist) {
                        $tagExist = new Tag();
                        $tagExist->setNom($tag);
                        $em->persist($tagExist);
                        $em->flush();
                    }

                    $essai->addTag($tagExist);
                }
            }
        } elseif (!isset($params["tags"])) {
            $essai->clearTags();
        }

        $medecin = $em->getRepository(Medecin::class)->find(isset($params["medecin"]["id"]) ? $params["medecin"]["id"] : 0);
        $essai->setMedecin($medecin);

        $arc = $em->getRepository(Arc::class)->find(isset($params["arc"]["id"]) ? $params["arc"]["id"] : 0);
        $essai->setArc($arc);

        foreach ($params as $key => $value) {
            if (is_array($value) || $value == '') {
                unset($params[$key]);
            }
        }

        if (isset($params["eudraCtNd"]) and $params["eudraCtNd"] == "true") {
            $essai->setEudraCtNd(true);
        } else {
            $essai->setEudraCtNd(false);
        }

        if (isset($params["ctNd"]) and $params["ctNd"] == "true") {
            $essai->setCtNd(true);
        } else {
            $essai->setCtNd(false);
        }

        if (isset($params["cancer"]) and $params["cancer"] == "true") {
            $essai->setCancer(true);
        } else {
            $essai->setCancer(false);
        }

        if (isset($params["sigaps"]) and $params["sigaps"] == "true") {
            $essai->setSigaps(true);
        } else {
            $essai->setSigaps(false);
        }

        if (isset($params["sigrec"]) and $params["sigrec"] == "true") {
            $essai->setSigrec(true);
        } else {
            $essai->setSigrec(false);
        }

        if (isset($params["emrc"]) and $params["emrc"] == "true") {
            $essai->setEmrc(true);
        } else {
            $essai->setEmrc(false);
        }

        $checkEssaiExist = $em->getRepository(Essais::class)->findBy(['nom' => $essai->getNom()]);

        if ($checkEssaiExist && !$essai->getId()) {
            return new JsonResponse(["success" => false, "message" => "Ce protocole existe déjà."]);
        }

        if (isset($new) && $new) {
            $em->persist($essai);
            $em->flush();
        } else {
            $em->flush();
        }

        return new JsonResponse(["success" => true, "protocole" => ["id" => $essai->getId()]]);
    }

    /**
     * @Route("/essais/export", name="exportEssais", options={"expose"=true})
     * @Security("has_role('ROLE_ARC')")
     * @param CsvToArray $export
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
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
        $emEssai = $em->getRepository(Essais::class);

        if ($truncate) {
            $em->createQuery('DELETE AppBundle:Essais e')->execute();
        }

        $file = $this->get('kernel')->getRootDir() . '/../bdd/essai.csv';
        $essais = $csvToArray->convert($file, ";");

        $bulkSize = 500;
        $i = 0;
        foreach ($essais as $e) {
            $i++;
            $essai = false;

            foreach ($e as $k => $v) {
                $e[$k] = trim($v);
            }

            if ($checkIfExist) {
                $exist = $emEssai->findOneBy(["nom" => $e["Nom de l'essai"]]);
                if ($exist) {
                    $essai = $exist;
                }
            }

            if (!$essai) {
                $essai = new Essais();
            }

            $dateOuverture = \DateTime::createFromFormat('d/m/Y', $e["Date d'ouverture"]);
            $dateFin = \DateTime::createFromFormat('d/m/Y', $e["Fin des inclusions"]);
            $dateCloture = \DateTime::createFromFormat('d/m/Y', $e["Date Cloture centre"]);
            $sigrec = (strtolower($e["Sigrec"]) == "vrai") ? true : false;
            $sigaps = (strtolower($e["Sigaps"]) == "vrai") ? true : false;
            $emrc = (strtolower($e["Emrc"]) == "vrai") ? true : false;
            $cancer = (strtolower($e["Cancer"]) == "vrai") ? true : false;
            $dateSignature = \DateTime::createFromFormat('d/m/Y', $e["Date signature convention"]);
            $e["N° Eudract"] = ($e["N° Eudract"] == '') ? null : $e["N° Eudract"];
            $e["N° Clinical trial"] = ($e["N° Clinical trial"] == '') ? null : $e["N° Clinical trial"];

            if (!$dateOuverture) {
                $dateOuverture = null;
            }

            if (!$dateFin) {
                $dateFin = null;
            }

            if (!$dateCloture) {
                $dateCloture = null;
            }

            if (!$dateSignature) {
                $dateSignature = null;
            }

            if ($e["Nom de l'essai"] == '') {
                continue;
            }

            $essai->setNom($e["Nom de l'essai"]);
            $essai->setTitre($e["Tiitre de l'essai"]);
            $essai->setNumeroCentre($e["Numero centre"]);
            $essai->setDateOuv($dateOuverture);
            $essai->setDateFinInc($dateFin);
            $essai->setDateClose($dateCloture);
            $essai->setStatut($e["Statut essai"]);
            $essai->setTypeEssai($e["Type d'essai"]);
            $essai->setStadeEss($e["Phase"]);
            $essai->setProm($e["Promoteur"]);
            $essai->setTypeProm($e["Type de promoteur"]);
            $essai->setContactNom($e["Nom du contact"]);
            $essai->setContactTel($e["Tel du contact"]);
            $essai->setContactMail($e["Mail du contact"]);
            $essai->setEcrfLink($e["Lien Ecrf"]);
            $essai->setNotes($e["Remarque essai"]);
            $essai->setUrcGes($e["gestion par l'URC"]);
            $essai->setSigrec($sigrec);
            $essai->setSigaps($sigaps);
            $essai->setEmrc($emrc);
            $essai->setCancer($cancer);
            $essai->setTypeConv($e["Type Convention"]);
            $essai->setDateSignConv($dateSignature);
            $essai->setNumEudract($e["N° Eudract"]);
            $essai->setNumCt($e["N° Clinical trial"]);
            $essaiDetail = new EssaiDetail();
            $essai->setDetail($essaiDetail);

            if ($medecin = $em->getRepository(Medecin::class)->findOneBy(["nom" => $e["Médecin référent- NOM"], "prenom" => $e["Médecin référent-Prénom"]])) {
                $essai->setMedecin($medecin);
            }

            $em->persist($essai);
            $em->persist($essaiDetail);

            if ($i % $bulkSize == 0) {
                $em->flush();
                $em->clear();
            }
        }

        $em->flush();
        $em->clear();

        return $this->redirectToRoute("listeEssais");
    }

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

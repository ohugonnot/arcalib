<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Arc;
use AppBundle\Entity\Essais;
use AppBundle\Entity\Inclusion;
use AppBundle\Entity\Medecin;
use AppBundle\Entity\Patient;
use AppBundle\Entity\Service;
use AppBundle\Form\InclusionType;
use AppBundle\Services\CsvToArray;
use AppBundle\Services\SendMail;
use DateTime as DateTime;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/arcalib")
 */
class InclusionController extends Controller
{

    // ------------------------------------------Delete Inclusion-----------------------------------------------------
    /**
     * @Route("/inclusion/supprimer/{id}", name="deleteInclusion", options={"expose"=true})
     * @Security("has_role('ROLE_ARC')")
     * @param $id
     * @return JsonResponse
     */
    public function deleteInclusionAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $emInclusion = $em->getRepository(Inclusion::class);
        $inclusion = $emInclusion->find($id);
        $em->remove($inclusion);
        $em->flush();

        return new JsonResponse(true);
    }

    // ------------------------------------------Liste Inclusion-----------------------------------------------------  

    /**
     * @Route("/inclusions/", name="listeInclusions")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listeInclusionsAction(Request $request)
    {
        $search = $request->query->get("recherche");
        if ($search == null) {
            $search = '%%';
            $searchId = '%%';
        } else {
            $searchId = '%%';
            if (preg_match("#id=#Ui", $search)) {
                $searchId = explode("id=", $search);
                $searchId = $searchId[1];
            }
        }

        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        $emInclusion = $em->getRepository(Inclusion::class);
        $query = $emInclusion->getQuery($user, $searchId, $search, [
            'statut' => $request->query->get("statut")
        ]);


        $paginator = $this->get('knp_paginator');
        $inclusions = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            20/*limit per page*/,
            array('defaultSortFieldName' => ['i.datInc'], 'defaultSortDirection' => 'desc')
        );

        return $this->render('inclusion/listeInclusions.html.twig', [
            'inclusions' => $inclusions
        ]);
    }

    /**
     * @Route("/inclusion/get/{id}", name="getInclusion", options={"expose"=true})
     * @param $id
     * @return JsonResponse
     */
    public function getInclusionAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $emInclusion = $em->getRepository(Inclusion::class);
        $inclusion = $emInclusion->findArray($id, $user);

        return new JsonResponse($inclusion);
    }


    /**
     * @Route("/inclusion/save/{id}", name="saveInclusion", options={"expose"=true})
     * @Security("has_role('ROLE_ARC')")
     * @param Request $request
     * @param null $id
     * @return JsonResponse
     */
    public function saveInclusionAction(Request $request, $id = null)
    {
        $em = $this->getDoctrine()->getManager();
        $inclusion = $em->getRepository(Inclusion::class)->find($id);
        if (!$inclusion) {
            $inclusion = new Inclusion();
            $new = true;
        }

        $form = $this->get('form.factory')->create(InclusionType::class, $inclusion);
        $form->handleRequest($request);

        $params = $request->request->get("appbundle_inclusion");
        $patient_id = $request->request->get("patient");

        if ($params["statut"] == "") {
            $params["statut"] = null;
        }
        if ($params["motifSortie"] == "") {
            $params["motifSortie"] = null;
        }

        $inclusion->setStatut($params["statut"]);
        $inclusion->setMotifSortie($params["motifSortie"]);

        if (isset($params["essai"]["id"])) {
            $essai = $em->getRepository('AppBundle:Essais')->find($params["essai"]["id"]);
            $inclusion->setEssai($essai);
        }

        if (isset($params["medecin"]["id"])) {
            $medecin = $em->getRepository('AppBundle:Medecin')->find($params["medecin"]["id"]);
            $inclusion->setMedecin($medecin);
        }

        if (isset($params["arc"]["id"])) {
            $arc = $em->getRepository('AppBundle:Arc')->find($params["arc"]["id"]);
            $inclusion->setArc($arc);
        }

        if ($patient_id) {
            $patient = $em->getRepository('AppBundle:Patient')->find($patient_id);
            $inclusion->setPatient($patient);
        }

        if (isset($params["service"]["id"])) {
            $service = $em->getRepository('AppBundle:Service')->find($params["service"]["id"]);
            $inclusion->setService($service);
        }

        if (isset($params["booRa"]) && $params["booRa"] == "true") {
            $inclusion->setBooRa(true);
        } else {
            $inclusion->setBooRa(false);
        }

        if (isset($params["booBras"]) && $params["booBras"] == "true") {
            $inclusion->setBooBras(true);
        } else {
            $inclusion->setBooBras(false);
        }

        foreach ($params as $key => $value) {
            if (is_array($value) || $value == '') {
                unset($params[$key]);
            }
        }

        if (isset($params["datCst"])) {
            $datCst = \DateTime::createFromFormat('d/m/Y', $params["datCst"])->settime(0, 0);
            if ($datCst != $inclusion->getDatCst()) {
                $inclusion->setDatCst($datCst);
            }
        }
        if (isset($params["datScr"])) {
            $datScr = \DateTime::createFromFormat('d/m/Y', $params["datScr"])->settime(0, 0);
            if ($datScr != $inclusion->getDatScr()) {
                $inclusion->setDatScr($datScr);
            }
        }
        if (isset($params["datInc"])) {
            $datInc = \DateTime::createFromFormat('d/m/Y', $params["datInc"])->settime(0, 0);
            if ($datInc != $inclusion->getDatInc()) {
                $inclusion->setDatInc($datInc);
            }
        }
        if (isset($params["datRan"])) {
            $datRan = \DateTime::createFromFormat('d/m/Y', $params["datRan"])->settime(0, 0);
            if ($datRan != $inclusion->getDatRan()) {
                $inclusion->setDatRan($datRan);
            }
        }
        if (isset($params["datJ0"])) {
            $datJ0 = \DateTime::createFromFormat('d/m/Y', $params["datJ0"])->settime(0, 0);
            if ($datJ0 != $inclusion->getDatJ0()) {
                $inclusion->setDatJ0($datJ0);
            }
        }
        if (isset($params["datOut"])) {
            $datOut = \DateTime::createFromFormat('d/m/Y', $params["datOut"])->settime(0, 0);
            if ($datOut != $inclusion->getDatOut()) {
                $inclusion->setDatOut($datOut);
            }
        }

        if (isset($new) && $new) {
            $em->persist($inclusion);
            $em->flush();
            $mailer = $this->get(SendMail::class);
            $mailer->sendEmail("default", [
                'sujet' => "Création d'une inclusion",
                "inclusion" => $inclusion,
                "user" => $this->getUser()
            ]);
        } else {
            $em->flush();
        }

        return new JsonResponse(["success" => true, "inclusion" => ["id" => $inclusion->getId()]]);
    }

    /**
     * @Route("/inclusion/editpartial/{id}", name="editInclusionPartial", options={"expose"=true})
     * @Security("has_role('ROLE_ARC')")
     * @param Request $request
     * @param Inclusion $inclusion
     * @return JsonResponse
     */
    public function editInclusionPartialAction(Request $request, Inclusion $inclusion)
    {
        $em = $this->getDoctrine()->getManager();

        $params = $request->request->get("appbundle_inclusion");
        $inclusion->setDatInc(DateTime::createFromFormat("d/m/Y", $params["datInc"]));
        $inclusion->setNumInc($params["numInc"]);
        $inclusion->setStatut($params["statut"]);

        $em->flush();

        return new JsonResponse(["success" => true, "inclusion" => ["id" => $inclusion->getId()]]);
    }

    /**
     * @Route("/inclusion/advanced/recherche/{query}", name="searchInclusions", options={"expose"=true})
     * @param Request $request
     * @param null $query
     * @return JsonResponse
     */
    public function searchInclusionsAction(Request $request, $query = null)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $query = explode(" ", $query);
        $filters = $request->request->get("filters");
        $emInclusion = $em->getRepository(Inclusion::class);
        $inclusions = $emInclusion->findAdvancedArray($query, $filters, $user);

        return new JsonResponse($inclusions);
    }

    /**
     * @Route("/inclusions/export", name="exportInclusions", options={"expose"=true})
     * @Security("has_role('ROLE_ADMIN')")
     * @param CsvToArray $export
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function exportInclusionsAction(CsvToArray $export)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $emInclusion = $em->getRepository(Inclusion::class);
        $inclusions = $emInclusion->findAllByUser($user);

        return $export->exportCSV($inclusions, "inclusions");
    }

    /**
     * @Route("/inclusions/export/all", name="exportInclusionsProtocole", options={"expose"=true})
     * @Security("has_role('ROLE_ADMIN')")
     * @param CsvToArray $export
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function exportInclusionsProtocoleAction(CsvToArray $export)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $emInclusion = $em->getRepository(Inclusion::class);
        $inclusions = $emInclusion->findAllByUser($user);

        return $export->exportCSV($inclusions, "inclusionsProtocole");
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
        $emInclusion = $em->getRepository(Inclusion::class);

        if ($truncate) {
            $em->createQuery('DELETE AppBundle:Inclusion i')->execute();
        }

        $file = $this->get('kernel')->getRootDir() . '/../bdd/inclusion.csv';
        $inclusions = $csvToArray->convert($file, ";");

        $bulkSize = 500;
        $i = 0;
        foreach ($inclusions as $inc) {
            $i++;
            $inclusion = false;

            // si une inclusion n'a pas un protocole et un patient je l'ignore
            if (empty($inc["Protocole"]) || empty($inc["Id Patient"])) {
                continue;
            }

            foreach ($inc as $k => $v) {
                $inc[$k] = trim($v);
            }

            $datScr = \DateTime::createFromFormat('d/m/Y', $inc["Date du screen"]);
            $datCst = \DateTime::createFromFormat('d/m/Y', $inc["Date du consentement"]);
            $datInc = \DateTime::createFromFormat('d/m/Y', $inc["Date d'inclusion"]);
            $datRan = \DateTime::createFromFormat('d/m/Y', $inc["Date de randomisation"]);
            $datJ0 = \DateTime::createFromFormat('d/m/Y', $inc["Date J0"]);
            $datOut = \DateTime::createFromFormat('d/m/Y', $inc["Date de sortie"]);

            $booRa = (strtolower($inc["Randomisation NA"]) == "vrai") ? true : false;

            if (!$datScr) {
                $datScr = null;
            }

            if (!$datCst) {
                $datCst = null;
            }

            if (!$datInc) {
                $datInc = null;
            }

            if (!$datRan) {
                $datRan = null;
            }

            if (!$datJ0) {
                $datJ0 = null;
            }

            if (!$datOut) {
                $datOut = null;
            }

            if ($checkIfExist) {
                $exist = $emInclusion->findOneBy(["idInterne" => $inc["N° inclusion table"]]);
                if ($exist) {
                    $inclusion = $exist;
                }
            }

            if (!$inclusion) {
                $inclusion = new Inclusion();
            }

            $inclusion->setNumInc($inc["N° inclusion"]);
            $inclusion->setIdInterne($inc["N° inclusion table"]);
            $inclusion->setDatScr($datScr);
            $inclusion->setDatCst($datCst);
            $inclusion->setDatInc($datInc);
            $inclusion->setDatRan($datRan);
            $inclusion->setDatJ0($datJ0);
            $inclusion->setDatOut($datOut);
            $inclusion->setStatut($inc["Statut du patient"]);
            $inclusion->setBooRa($booRa);
            $inclusion->setBraTrt($inc["Bras de traitement"]);
            $inclusion->setMotifSortie($inc["Cause de sortie"]);

            if ($medecin = $em->getRepository(Medecin::class)->findOneBy(["NomPrenomConcat" => $inc["Médecin responsable de l'Inclusion"]])) {
                $inclusion->setMedecin($medecin);
            }

            if ($patient = $em->getRepository(Patient::class)->findOneBy(["idInterne" => $inc["Id Patient"]])) {
                $inclusion->setPatient($patient);
            }

            if ($essai = $em->getRepository(Essais::class)->findOneBy(["nom" => $inc["Protocole"]])) {
                $inclusion->setEssai($essai);
            }

            if ($service = $em->getRepository(Service::class)->findOneBy(["nom" => $inc["service"]])) {
                $inclusion->setService($service);
            }

            if ($arc = $em->getRepository(Arc::class)->findOneBy(["iniArc" => $inc["ArcInc"]])) {
                $inclusion->setArc($arc);
            }

            $em->persist($inclusion);

            if ($i % $bulkSize == 0) {
                $em->flush();
                $em->clear();
            }
        }

        $em->flush();
        $em->clear();

        return $this->redirectToRoute("listeInclusions");
    }
}

<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Arc;
use AppBundle\Entity\Essais;
use AppBundle\Entity\LibCim10;
use AppBundle\Entity\Medecin;
use AppBundle\Entity\Patient;
use AppBundle\Entity\Service;
use AppBundle\Form\PatientType;
use AppBundle\Services\CsvToArray;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/arcalib")
 */
class PatientController extends Controller
{

    /**
     * @Route("/patient/edit/{id}", name="editPatient", options={"expose"=true})
     * @Route("/inclusion/editer/{id}", name="editInclusion", options={"expose"=true})
     * @Route("/patient", name="patient", options={"expose"=true})
     * @param null $id
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function patientAction($id = null)
    {
        if ($id) {
            $this->redirectToRoute("patient", ["id" => $id]);
        }

        $medecins = $this->getDoctrine()->getManager()->getRepository(Medecin::class)->findBy([], ["nom" => 'asc']);
        $libCim10 = $this->getDoctrine()->getManager()->getRepository(LibCim10::class)->findBy(["utile" => true], ["libCourt" => 'asc']);
        $essais = $this->getDoctrine()->getManager()->getRepository(Essais::class)->findBy([], ["nom" => 'asc']);
        $services = $this->getDoctrine()->getManager()->getRepository(Service::class)->findBy([], ["nom" => 'asc']);
        $arcs = $this->getDoctrine()->getManager()->getRepository(Arc::class)->findBy([], ["nomArc" => 'asc']);

        return $this->render('patient/patient.html.twig', [
            'medecins' => $medecins,
            'libCim10' => $libCim10,
            'essais' => $essais,
            'services' => $services,
            'arcs' => $arcs
        ]);
    }

    // ------------------------------------------Delete Patient-----------------------------------------------------

    /**
     * @Route("/patient/supprimer/{id}", name="deletePatient", options={"expose"=true})
     * @Security("has_role('ROLE_ARC')")
     * @param $id
     * @return JsonResponse
     */
    public function deletePatientAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $emPatient = $em->getRepository(Patient::class);
        $patient = $emPatient->find($id);

        $em->remove($patient);
        $em->flush();

        return new JsonResponse(true);
    }

    // ------------------------------------------Liste Patient-----------------------------------------------------

    /**
     * @Route("/patients/", name="listePatients")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listePatientsAction(Request $request)
    {
        $search = $request->query->get("recherche");
        if ($search == null) {
            $search = '%%';
        }

        $user = $this->getUser();
        $em = $this->getDoctrine()->getManager();
        $emPatient = $em->getRepository(Patient::class);
        $query = $emPatient->getQuery($user, $search);

        $paginator = $this->get('knp_paginator');
        $patients = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            20/*limit per page*/,
            array('defaultSortFieldName' => ['p.nom', 'p.prenom'], 'defaultSortDirection' => 'asc')
        );

        return $this->render('patient/listePatients.html.twig', [
            'patients' => $patients
        ]);
    }

    /**
     * @Route("/patient/select/{id}", name="selectPatient", options={"expose"=true})
     * @param $id
     * @return JsonResponse
     */
    public function selectPatientAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        $emPatient = $em->getRepository(Patient::class);
        $patient = $emPatient->findArray($id, $user);

        return new JsonResponse($patient);
    }

    /**
     * @Route("/patient/save/{id}", name="savePatient", options={"expose"=true})
     * @Security("has_role('ROLE_ARC')")
     * @param Request $request
     * @param null $id
     * @return JsonResponse
     */
    public function savePatientAction(Request $request, $id = null)
    {
        $em = $this->getDoctrine()->getManager();
        $patient = $em->getRepository(Patient::class)->find($id);
        if (!$patient) {
            $patient = new Patient();
            $new = true;
        }

        $form = $this->get('form.factory')->create(PatientType::class, $patient);
        $form->handleRequest($request);

        $params = $request->request->get("appbundle_patient");

        $l10 = $em->getRepository(LibCim10::class)->find($params["libCim10"]["id"]);
        $patient->setLibCim10($l10);

        $medecin = $em->getRepository(Medecin::class)->find($params["medecin"]["id"]);
        $patient->setMedecin($medecin);

        foreach ($params as $key => $value) {
            if (is_array($value) || $value == '') {
                unset($params[$key]);
            }
        }

        if (isset($params["datNai"])) {
            $datNai = \DateTime::createFromFormat('d/m/Y', $params["datNai"])->settime(0,0);
            if($datNai != $patient->getDatNai()) {
                $patient->setDatNai($datNai);
            }
        }

        if (isset($params["datDiag"])) {
            $datDiag = \DateTime::createFromFormat('d/m/Y', $params["datDiag"])->settime(0,0);
            if($datDiag != $patient->getDatDiag()) {
                $patient->setDatDiag($datDiag);
            }
        }
        if (isset($params["datLast"])) {
            $datLast = \DateTime::createFromFormat('d/m/Y', $params["datLast"])->settime(0,0);
            if($datLast != $patient->getDatLast()) {
                $patient->setDatLast($datLast);
            }
        }
        if (isset($params["datDeces"])) {
            $datDeces = \DateTime::createFromFormat('d/m/Y', $params["datDeces"])->settime(0,0);
            if($datDeces != $patient->getDatDeces()) {
                $patient->setDatDeces($datDeces);
            }
        }

        if (isset($params["cancer"]) and $params["cancer"] == "true") {
            $patient->setCancer(true);
        } else {
            $patient->setCancer(false);
        }

        $checkPatientExist = $em->getRepository(Patient::class)->alreadyExist([
            'nom' => $patient->getNom(),
            'prenom' => $patient->getPrenom(),
            'datNai' => $patient->getDatNai(),
        ]);

        if ($checkPatientExist && !$patient->getId()) {
            return new JsonResponse(["success" => false, "message" => "Ce patient existe déjà."]);
        }
        if (isset($new) && $new) {
            $em->persist($patient);
            $em->flush();
        } else {
            $em->flush();
        }

        $em->flush();

        return new JsonResponse(["success" => true, "patient" => ["id" => $patient->getId()]]);
    }

    /**
     * @Route("/patient/advanced/recherche/{query}", name="searchPatients", options={"expose"=true})
     * @param Request $request
     * @param null $query
     * @return JsonResponse
     */
    public function searchPatientsAction(Request $request, $query = null)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $filters = $request->request->get("filters");
        $emPatient = $em->getRepository(Patient::class);
        $patients = $emPatient->findAdvancedArray($query, $filters, $user);

        return new JsonResponse($patients);
    }

    // fonction recherche des patients

    /**
     * @Route("/patient/recherche/{query}", name="recherchePatient", options={"expose"=true})
     * @param $query
     * @return JsonResponse
     */
    public function recherchePatientAction($query)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();

        $emPatient = $em->getRepository(Patient::class);
        $patients = $emPatient->findByNomPrenom($query, $user);

        return new JsonResponse($patients);
    }

    // fonction Exportation de la liste des patients

    /**
     * @Route("/patients/export", name="exportPatients", options={"expose"=true})
     * @Security("has_role('ROLE_ARC')")
     * @param CsvToArray $export
     * @return \Symfony\Component\HttpFoundation\StreamedResponse
     */
    public function exportPatientsAction(CsvToArray $export)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $emPatient = $em->getRepository(Patient::class);
        $patients = $emPatient->findAllByUser($user);

        return $export->exportCSV($patients, "patients");
    }


    /**
     * @param CsvToArray $csvToArray
     * @param bool $checkIfExist
     * @param bool $truncate
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     * @throws \Doctrine\Common\Persistence\Mapping\MappingException
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\ORMException
     * @throws \Doctrine\ORM\OptimisticLockException
     */
    public function importAction(CsvToArray $csvToArray, $checkIfExist = true, $truncate = true)
    {
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $emPatient = $em->getRepository(Patient::class);

        if ($truncate) {
            $em->createQuery('DELETE AppBundle:Patient p')->execute();
        }

        $file = $this->get('kernel')->getRootDir() . '/../bdd/patient.csv';
        $patients = $csvToArray->convert($file, ";");

        $bulkSize = 500;
        $i = 0;
        foreach ($patients as $p) {
            $i++;
            $patient = false;

            // Si le patient n'a pas un nom et un prénom on l'ignore
            if (empty($p["NOM Patient"]) || empty($p["Prénom Patient"])) {
                continue;
            }

            foreach ($p as $k => $v) {
                $p[$k] = trim($v);
            }

            $datNai = \DateTime::createFromFormat('d/m/Y', $p["Date de naissance"]);
            $datDiag = \DateTime::createFromFormat('d/m/Y', $p["Date du diagnostic"]);
            $datLast = \DateTime::createFromFormat('d/m/Y', $p["Date dernières nouvelles"]);
            $datDeces = \DateTime::createFromFormat('d/m/Y', $p["Date  Décès"]);
            $cancer = (strtolower($p["Cancer O/N"]) == "vrai") ? true : false;;

            if (!$datNai) {
                $datNai = null;
            }

            if (!$datDiag) {
                $datDiag = null;
            }

            if (!$datLast) {
                $datLast = null;
            }

            if (!$datDeces) {
                $datDeces = null;
            }

            if ($checkIfExist) {
                $exist = $emPatient->findOneBy(["nom" => $p["NOM Patient"], "prenom" => $p["Prénom Patient"], "datNai" => $datNai]);
                if ($exist) {
                    $patient = $exist;
                }
            }

            if (!$patient) {
                $patient = new Patient();
            }

            $patient->setIdInterne($p["Id Patient"]);
            $patient->setNom($p["NOM Patient"]);
            $patient->setPrenom($p["Prénom Patient"]);
            $patient->setDatNai($datNai);
            $patient->setDatDiag($datDiag);
            $patient->setDatLast($datLast);
            $patient->setDatDeces($datDeces);
            $patient->setNomNaissance($p["NOM JF"]);
            $patient->setIpp($p["IPP"]);
            $patient->setSexe($p["Sexe"]);
            $patient->setMemo($p["Notes Patient"]);
            $patient->setTxtDiag($p["Diagnostic"]);
            $patient->setCancer($cancer);
            $patient->setEvolution($p["Evolution"]);
            $patient->setDeces($p["Vivant ou décédé"]);

            if ($medecin = $em->getRepository(Medecin::class)->findByNomPrenomConcat($p["Médecin référent"])) {
                $patient->setMedecin($medecin);
            }

            if ($libCim10 = $em->getRepository(LibCim10::class)->findOneBy(["cim10code" => $p["Diagnostic CIM10"]])) {
                $patient->setLibCim10($libCim10);
            }

            $em->persist($patient);;

            if ($i % $bulkSize == 0) {
                $em->flush();
                $em->clear();
            }
        }

        $em->flush();
        $em->clear();

        return $this->redirectToRoute("listePatients");
    }
}
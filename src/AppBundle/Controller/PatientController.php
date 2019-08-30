<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Arc;
use AppBundle\Entity\Essais;
use AppBundle\Entity\LibCim10;
use AppBundle\Entity\Medecin;
use AppBundle\Entity\Patient;
use AppBundle\Entity\Service;
use AppBundle\Factory\PatientFactory;
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
	 * @param Patient $patient
	 * @return JsonResponse
	 */
    public function deletePatientAction(Patient $patient)
    {
        $em = $this->getDoctrine()->getManager();

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
	 * @param PatientFactory $patientFactory
	 * @return JsonResponse
	 */
    public function savePatientAction(Request $request, PatientFactory $patientFactory, $id = null)
    {
        $em = $this->getDoctrine()->getManager();
        $patient = $em->getRepository(Patient::class)->find($id);
        if (!$patient) {
            $patient = new Patient();
            $em->persist($patient);
            $new = true;
        }

	    $patient = $patientFactory->hydrate($patient, $request->request->get("appbundle_patient"));

        if (isset($patient->errorsMessage) && $patient->errorsMessage)
            return new JsonResponse(["success" => false, "message" => $patient->errorsMessage]);

        if (isset($new) && $new)
            $em->persist($patient);
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
}
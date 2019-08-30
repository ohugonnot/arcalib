<?php

namespace AppBundle\Controller;

use AppBundle\Import\ArcImport;
use AppBundle\Import\EssaiImport;
use AppBundle\Import\InclusionImport;
use AppBundle\Import\MedecinImport;
use AppBundle\Import\PatientImport;
use AppBundle\Import\ServiceImport;
use AppBundle\Import\VisiteImport;
use AppBundle\Services\AnonymisationPatient;
use AppBundle\Services\ViderCache;
use Doctrine\ORM\NonUniqueResultException;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

class DefaultController extends Controller
{

    /**
     * @Route("/mentions-legales", name="mentionsLegales")
     * @return Response
     */
    public function mentionsLegalesAction()
    {
        return $this->render('pages/mentions-legales.html.twig');
    }

    /**
     * @Route("/support", name="support")
     * @return Response
     */
    public function supportAction()
    {
        return $this->render('pages/support.html.twig');
    }

	/**
	 * @Route("/create/custom/patient", name="createCustomPatient")
	 * @param AnonymisationPatient $anonymisationPatient
	 * @return JsonResponse
	 */
    public function generateCustomPatient(AnonymisationPatient $anonymisationPatient)
    {
        $isAnonym = $anonymisationPatient->generateCustomPatient();
        $message = ($isAnonym) ? "Les patients ont étés anonymisés" : "Erreur lors de l'anonymisation";

        return new JsonResponse(["Message" => $message]);
    }


	/**
	 * @Route("/vider/cache", name="viderCache")
	 * @param ViderCache $viderCache
	 * @return JsonResponse
	 */
    public function viderCache(ViderCache $viderCache)
    {
		$message = $viderCache->viderCache();

        return new JsonResponse(["Message" => $message]);
    }

	/**
	 * @param ArcImport $arcImport
	 * @param ServiceImport $serviceImport
	 * @param MedecinImport $medecinImport
	 * @param InclusionImport $inclusionImport
	 * @param PatientImport $patientImport
	 * @param VisiteImport $visiteImport
	 * @param EssaiImport $essaiImport
	 * @throws NonUniqueResultException
	 */
	public function importAll(ArcImport $arcImport,
	                          ServiceImport $serviceImport,
	                          MedecinImport $medecinImport,
	                          InclusionImport $inclusionImport,
	                          PatientImport $patientImport,
	                          VisiteImport $visiteImport,
	                          EssaiImport $essaiImport)
    {
    	$serviceImport->import();
    	$arcImport->import();
    	$medecinImport->import();
    	$essaiImport->import();
    	$patientImport->import();
    	$inclusionImport->import();
    	$visiteImport->import();
    }
}

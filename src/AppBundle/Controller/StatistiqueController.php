<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Essais;
use AppBundle\Entity\Inclusion;
use AppBundle\Entity\Patient;
use AppBundle\Entity\ValidationErreur;
use AppBundle\Entity\Visite;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/arcalib")
 */
class StatistiqueController extends Controller
{

    /**
     * @Route("/analyse/statistiques", name="analyseStatistique")
     * @param Request $request
     * @return Response
     */
    public function analyseStatistiqueAction(Request $request)
    {
        $em = $this->getDoctrine()->getManager();
        $emEssai = $em->getRepository(Essais::class);
        $emInclusion = $em->getRepository(Inclusion::class);
        $emVisite = $em->getRepository(Visite::class);
        $emPatient = $em->getRepository(Patient::class);
        $emValidation = $em->getRepository(ValidationErreur::class);

        $nbPatient = $emPatient->findAll();
        $nbEssai = $emEssai->findAll();
        $nbEssaiOuvert = $emEssai->findByStatut(Essais::INCLUSIONS_OUVERTES);
        $nbEssaiActif = $emEssai->findActif();
        $nbValidationIgnored = $emValidation->findAll();
        $nbPatientSuivis = $emPatient->findPatientSuivis();

        $verification = $this->forward('AppBundle\Controller\VerificationController::verificationDataAction', ["api" => true]);
        $nbErreurs = json_decode($verification->getContent(), true);

        if ($request->isMethod('POST')) {

            $debut = \DateTime::createFromFormat("d/m/Y", $request->get("dateDebut"));
            $fin = \DateTime::createFromFormat("d/m/Y", $request->get("dateFin"));

            if ($debut && $fin) {
                $nbOuverture = $emEssai->findEssaiByDateOuverture($debut, $fin);
                $nbCloture = $emEssai->findEssaiByDateCloture($debut, $fin);
                $nbInclusion = $emInclusion->findByDate($debut, $fin);
                $nbVisite = $emVisite->findByDate($debut, $fin);
            }
        }

        return $this->render('statistiques/statistiques.html.twig', [
            "nbOuverture" => $nbOuverture ?? [],
            "nbCloture" => $nbCloture  ?? [],
            "nbInclusion" => $nbInclusion  ?? [],
            "nbVisite" => $nbVisite  ?? [],
            "nbPatient" => $nbPatient,
            "nbEssai" => $nbEssai,
            'nbEssaiOuvert' => $nbEssaiOuvert,
            "nbEssaiActif" => $nbEssaiActif,
            "nbValidationIgnored" => $nbValidationIgnored,
            'nbErreurs' => $nbErreurs,
            'nbPatientSuivis' => $nbPatientSuivis
        ]);
    }
}
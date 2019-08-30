<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Arc;
use AppBundle\Entity\Essais;
use AppBundle\Entity\Facture;
use AppBundle\Entity\Inclusion;
use AppBundle\Entity\Medecin;
use AppBundle\Entity\Patient;
use AppBundle\Entity\ValidationErreur;
use AppBundle\Entity\Visite;
use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * TODO REFACTO LE CONTROLLEUR EN MODE SERVICE
 * @property array erreurs
 * @Route("/arcalib")
 */
class VerificationController extends Controller
{

    /**
     * @Route("/verification/data/{api}", name="verificationData")
     * @param bool $api
     * @return JsonResponse|Response
     */
    public function verificationDataAction($api = false)
    {
        $em = $this->getDoctrine()->getManager();
        $this->erreurs = [];
        $count = 0;

        $arcs = $em->getRepository(Arc::class)->findAll();
        $essais = $em->getRepository(Essais::class)->findAll();
        $inclusions = $em->getRepository(Inclusion::class)->findAll();
        $patients = $em->getRepository(Patient::class)->findAll();
        $medecins = $em->getRepository(Medecin::class)->findAll();
        $factures = $em->getRepository(Facture::class)->findAll();
        $visites = $em->getRepository(Visite::class)->findAll();

        /** @var Arc[] $arcs */
        foreach ($arcs as $arc) {
            $this->VerificationArc($arc);
        }
        /** @var Essais[] $essais */
        foreach ($essais as $essai) {
            $this->VerificationEssai($essai);
        }
        /** @var Inclusion[] $inclusions */
        foreach ($inclusions as $inclusion) {
            $this->VerificationInclusion($inclusion);
        }
        /** @var Patient[] $patients */
        foreach ($patients as $patient) {
            $this->VerificationPatient($patient);
        }
        /** @var Facture[] $factures */
        foreach ($factures as $facture) {
            $this->VerificationFacture($facture);
        }
        /** @var Visite[] $visites */
        foreach ($visites as $visite) {
            $this->VerificationVisite($visite);
        }


        /** @var ArrayCollection $validationErreurs */
        $validationErreurs = new ArrayCollection($em->getRepository(ValidationErreur::class)->findAll());

        foreach ($this->erreurs as $type => $array) {

            foreach ($array as $id => $array2) {

                foreach ($array2 as $erreur => $message) {
                    $exist = $validationErreurs->filter(
                        function (ValidationErreur $validationErreur) use ($type, $id, $erreur, $message) {
                            if ($validationErreur->getType() == $type and $validationErreur->getEntite() == $id and $validationErreur->getErreur() == $erreur) {
                                $validationErreur->setMessage($message);

                                return true;
                            } else {

                                return false;
                            }
                        });
                    if (!$exist->isEmpty()) {
                        unset($this->erreurs[$type][$id][$erreur]);
                        if (empty($this->erreurs[$type][$id])) {
                            unset($this->erreurs[$type][$id]);
                        }
                        if (empty($this->erreurs[$type])) {
                            unset($this->erreurs[$type]);
                        }
                    } else {
                        $count++;
                    }
                }
            }
        }

        if ($api) {
            return new JsonResponse($count);
        }

        return $this->render('verification/liste.html.twig', [
            "erreurs" => $this->erreurs,
            'arcs' => $this->orderById($arcs),
            'essais' => $this->orderById($essais),
            'inclusions' => $this->orderById($inclusions),
            'patients' => $this->orderById($patients),
            'factures' => $this->orderById($factures),
            'medecins' => $this->orderById($medecins),
            'visites' => $this->orderById($visites),
            'validationErreurs' => $validationErreurs,
            'nbErreurs' => $count,
        ]);
    }

    private function VerificationArc(Arc $arc)
    {
        if ($arc->getDatIn() == null) {
            $this->erreurs["arc"][$arc->getId()][1] = "a1-La date d'arrivée de l'ARC ne peut pas être nulle";
        }

        if ($arc->getDatIn() != null and $arc->getDatOut() != null and $arc->getDatIn() >= $arc->getDatOut()) {
            $this->erreurs["arc"][$arc->getId()][2] = "a2-La date d'arrivée doit être antérieure à la date de départ de l'ARC";
        }

        if (!filter_var($arc->getMail(), FILTER_VALIDATE_EMAIL) and $arc->getMail() != null) {
            $this->erreurs["arc"][$arc->getId()][3] = "a3-L'email de l'ARC n'est pas valide";
        }
    }

    private function VerificationEssai(Essais $essai)
    {
        if ($essai->getStatut() == null) {
            $this->erreurs["essai"][$essai->getId()][1] = "e1-Le statut de l'essai ne peut pas être vide";
        }

        if ($essai->getDateOuv() < '01/01/2010' && $essai->getDateOuv() != null) {
            $this->erreurs["essai"][$essai->getId()][2] = "e2-La Date de Mise en place de l'essai est anterieure au 01/01/2010";
        }

        if ($essai->getStatut() == Essais::INCLUSIONS_OUVERTES and $essai->getDateOuv() == null) {
            $this->erreurs["essai"][$essai->getId()][3] = "e3-Compléter la date d'ouverture de l'essai quand le statut de l'essai est 'Ouvert'";
        }

        if ($essai->getStatut() == Essais::INCLUSIONS_CLOSES_SUIVI and $essai->getDateFinInc() == null) {
            $this->erreurs["essai"][$essai->getId()][4] = "e4-Compléter la date de fin des inclusions quand l'essai est 'clos aux inclusions'";
        }

        if ($essai->getStatut() == Essais::ARCHIVE and $essai->getDateClose() == null) {
            $this->erreurs["essai"][$essai->getId()][5] = "e5-Compléter la date de clôture quand le centre est fermé ou archivé'";
        }

        if ($essai->getStatut() == Essais::INCLUSIONS_OUVERTES and $essai->getNumeroCentre() == null) {
            $this->erreurs["essai"][$essai->getId()][6] = "e6-Le N° de centre n'est pas renseigné alors que le statut de l'etude est > 'Ouvert'";
        }

        if ($essai->getDateOuv() != null and $essai->getDateFinInc() != null and $essai->getDateOuv() >= $essai->getDateFinInc()) {
            $this->erreurs["essai"][$essai->getId()][7] = "e7-La date de fin des inclusions doit être posterieure à la date d'ouverture de l'essai";
        }

        if ($essai->getContactNom() == null) {
            $this->erreurs["essai"][$essai->getId()][8] = "e8-Le nom du contact de l'essai n'est pas renseigné";
        }

        if ($essai->getContactMail() == null) {
            $this->erreurs["essai"][$essai->getId()][9] = "e9-Le Mail du contact de l'essai n'est pas renseigné";
        }

        if ($essai->getContactTel() == null) {
            $this->erreurs["essai"][$essai->getId()][10] = "e10-Le Téléphone du contact de l'essai n'est pas renseigné";
        }

        if ($essai->getArc() == null) {
            $this->erreurs["essai"][$essai->getId()][11] = "e11-Aucun ARC n'a été renseigné pour ce projet";
        }

        if ($essai->getMedecin() == null) {
            $this->erreurs["essai"][$essai->getId()][12] = "e12-Aucun PI n'a été renseigné pour ce projet";
        }

        if ($essai->getTypeProm() == null) {
            $this->erreurs["essai"][$essai->getId()][13] = "e13-Le type du promoteur n'a pas été renseigné ";
        }

        if ($essai->getStatut() == Essais::REFUS && !$essai->getInclusions()->isEmpty()) {
            $this->erreurs["essai"][$essai->getId()][14] = "e14-Les protocole avec un statut REFUS ne doivent pas avoir d'inclusion.";
        }

    }

    private function VerificationInclusion(Inclusion $inclusion)
    {
        if ($inclusion->getStatut() == null) {
            $this->erreurs["inclusion"][$inclusion->getId()][1] = "i1-Le statut de l'inclusion n'est pas renseigné";
        }

        if ($inclusion->getDatOut() != null and $inclusion->getDatInc() != null and $inclusion->getDatInc() > $inclusion->getDatOut()) {
            $this->erreurs["inclusion"][$inclusion->getId()][2] = "i2-La date de sortie de l'étude doit être posterieure à la date d'inclusion";
        }

        if ($inclusion->getStatut() == Inclusion::OUI_EN_COURS and $inclusion->getNumInc() == null) {
            $this->erreurs["inclusion"][$inclusion->getId()][3] = "i3-Pas de n° d'inclusion pour ce patient.";
        }

        if ($inclusion->getStatut() == Inclusion::OUI_SORTIE and $inclusion->getDatOut() == null) {
            $this->erreurs["inclusion"][$inclusion->getId()][4] = "i4-Le patient est sorti de l'étude mais sans date de sortie";
        }

        if ($inclusion->getDatOut() != null and $inclusion->getStatut() != Inclusion::OUI_SORTIE) {
            $this->erreurs["inclusion"][$inclusion->getId()][5] = "i5-Date de sortie, mais pas de statut 'sortie'";
        }

        if ($inclusion->getStatut() == Inclusion::OUI_SORTIE and $inclusion->getMotifSortie() == null) {
            $this->erreurs["inclusion"][$inclusion->getId()][6] = "i6-Le motif de sortie du patient n'est pas documenté";
        }

        if ($inclusion->getMedecin() == null) {
            $this->erreurs["inclusion"][$inclusion->getId()][7] = "i7-Aucun médecin n'a été renseigné";
        }

        if ($inclusion->getArc() == null) {
            $this->erreurs["inclusion"][$inclusion->getId()][8] = "i8-Aucun arc n'a été renseigné";
        }

        if ($inclusion->getService() == null) {
            $this->erreurs["inclusion"][$inclusion->getId()][9] = "i9-Aucun service n'a été renseigné";
        }

        if ($inclusion->getStatut() == Inclusion::OUI_EN_COURS && $inclusion->getEssai() != null
            && in_array($inclusion->getEssai()->getStatut(), [Essais::ARCHIVE, Essais::QUERIES_ET_FINALISATION])) {
            $this->erreurs["inclusion"][$inclusion->getId()][10] = "i10-Le statut ne peux pas être Oui, en cours si l'essai est archivé ou en finalisation";
        }
    }

    // *********************************************Verification ARC************************************
    private function VerificationPatient(Patient $patient)
    {
        if ($patient->getDatNai() == null) {
            $this->erreurs["patient"][$patient->getId()][1] = "p1-La date de naissance du patient n'est pas documentée";
        }

        if ($patient->getDatNai() <= DateTime::createFromFormat('d/m/Y', '01/01/1920')) {
            $this->erreurs["patient"][$patient->getId()][2] = "p2-Vérifier la date de naissance du patient: DDN<1920";
        }

        if ($patient->getSexe() == null) {
            $this->erreurs["patient"][$patient->getId()][3] = "p3-le sexe du patient n'est pas renseigné";
        }

        if ($patient->getDeces() == Patient::DECEDE and $patient->getDatDeces() == null) {
            $this->erreurs["patient"][$patient->getId()][4] = "p4-La date de décès est absente alors que ce patient a le statut 'Décédé'";
        }

        if ($patient->getMedecin() == null) {
            $this->erreurs["patient"][$patient->getId()][5] = "p5-Aucun médecin référent n'est renseigné pour ce patient";
        }
    }

    // *********************************************Verification ESSAIS************************************

    private function VerificationFacture(Facture $facture)
    {
        if ($facture->getDate() == null) {
            $this->erreurs["facture"][$facture->getId()][1] = "f1-La date de la facture n'est pas renseignée";
        }

        if ($facture->getNumero() == null) {
            $this->erreurs["facture"][$facture->getId()][2] = "f2-Pas de n° de facture";
        }

        if ($facture->getStatut() == null) {
            $this->erreurs["facture"][$facture->getId()][3] = "f3-La facture n'a pas de statut";
        }

        if ($facture->getStatut() == Facture::PAYE and $facture->getDateCaisse() == null) {
            $this->erreurs["facture"][$facture->getId()][4] = "f4-Statut payé sans date d'encaissement ";
        }

        if ($facture->getDateCaisse() != null and $facture->getStatut() != Facture::PAYE) {
            $this->erreurs["facture"][$facture->getId()][5] = "f5-Date de payement mais sans le statut 'Payé'";
        }

        if ($facture->getEssai() == null) {
            $this->erreurs["facture"][$facture->getId()][6] = "f6-La facture n'est pas reliée à un projet";
        }
    }

// *********************************************Verification INCLUSION************************************

    private function VerificationVisite(Visite $visite)
    {
        if ($visite->getDate() == null) {
            $this->erreurs["visite"][$visite->getId()][1] = "v1-La date de la visite n'a pas été documentée";
        }

        if ($visite->getStatut() == null) {
            $this->erreurs["visite"][$visite->getId()][2] = "v2-Le statut de la visite n'a pas été documenté";
        }

        if ($visite->getType() == null) {
            $this->erreurs["visite"][$visite->getId()][3] = "v3-Le type de la visite n'a pas été documenté";
        }

        if ($visite->getDate() <= DateTime::createFromFormat('d/m/Y', '01/01/2010')) {
            $this->erreurs["visite"][$visite->getId()][4] = "v4-La date de la visite < 2010. Confirmez?";
        }
    }

    // *********************************************Verification PATIENT************************************

    private function orderById($array)
    {
        $newArray = [];
        foreach ($array as $entity) {
            $newArray[$entity->getId()] = $entity;
        }

        return $newArray;
    }

    // *********************************************Verification visite************************************

    /**
     * @Route("/verification/save/{type}/{entite}/{erreur}", name="verificationSave", options={"expose"=true})
     * @Security("has_role('ROLE_ARC')")
     * @param Request $request
     * @param $type
     * @param $entite
     * @param $erreur
     * @return JsonResponse
     */
    public function saveValidation(Request $request, $type, $entite, $erreur)
    {
        $message = $request->request->get("message");
        $validationErreur = new ValidationErreur();
        $validationErreur->setType($type)->setEntite($entite)->setErreur($erreur)->setMessage($message);
        $em = $this->getDoctrine()->getManager();
        $em->persist($validationErreur);
        $em->flush();

        return new JsonResponse(array('message' => "La validation à été sauvegardé", "id" => $validationErreur->getId()));
    }

// *********************************************Verification Facture************************************

    /**
     * @Route("/verification/unsave/{erreurId}", name="verificationUnsave", options={"expose"=true})
     * @Security("has_role('ROLE_ARC')")
     * @param $erreurId
     * @return JsonResponse
     */
    public function unsaveValidation($erreurId)
    {
        $em = $this->getDoctrine()->getManager();
        $anomalie = $em->getRepository(ValidationErreur::class)->find($erreurId);
        $em->remove($anomalie);
        $em->flush();

        return new JsonResponse(array('message' => "La validation à été supprimé"));
    }
}


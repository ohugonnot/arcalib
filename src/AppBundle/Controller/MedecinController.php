<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Inclusion;
use AppBundle\Entity\Medecin;
use AppBundle\Entity\Service;
use AppBundle\Form\MedecinType;
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
class MedecinController extends Controller
{
    /**
     * @Route("/medecin/ajouter", name="addMedecin")
     * @Security("has_role('ROLE_ARC')")
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function addMedecinAction(Request $request)
    {
        $medecin = new Medecin();

        $form = $this->get('form.factory')->create(MedecinType::class, $medecin);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($medecin);
            $em->flush();

            return $this->redirectToRoute("listeMedecins");
        }

        return $this->render('medecin/editMedecin.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/medecin/editer/{id}", name="editMedecin", options={"expose"=true})
     * @Security("has_role('ROLE_ARC')")
     * @param Request $request
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse|\Symfony\Component\HttpFoundation\Response
     */
    public function editMedecinAction(Request $request, $id)
    {
        $em = $this->getDoctrine()->getManager();
        $emMedecin = $em->getRepository(Medecin::class);
        $inclusions = $em->getRepository(Inclusion::class)->findAll();
        $inclusionMedecin = $em->getRepository(Inclusion::class)->findBy(['medecin' => $id]);
        $medecin = $emMedecin->find($id);
        $form = $this->get('form.factory')->create(MedecinType::class, $medecin);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {

            if (!$this->isGranted('ROLE_ARC')) {
                throw $this->createAccessDeniedException("Vous n'avez pas le droit d'éditer un Medecin");
            }

            $em->flush();
            return $this->redirectToRoute("listeMedecins");
        }

        return $this->render('medecin/editMedecin.html.twig', [
            'form' => $form->createView(),
            'informations' => ['total' => count($inclusions), 'medecin' => count($inclusionMedecin), 'essais' => $medecin->getEssais()]
        ]);
    }

    /**
     * @Route("/medecin/supprimer/{id}", name="deleteMedecin", options={"expose"=true})
     * @Security("has_role('ROLE_ARC')")
     * @param $id
     * @return \Symfony\Component\HttpFoundation\RedirectResponse
     */
    public function deleteMedecinAction($id)
    {
        $em = $this->getDoctrine()->getManager();
        $emMedecin = $em->getRepository(Medecin::class);
        $medecin = $emMedecin->find($id);
        $em->remove($medecin);
        $em->flush();

        return $this->redirectToRoute("listeMedecins");
    }

    /**
     * @Route("/medecins/", name="listeMedecins", options={"expose"=true})
     * @param Request $request
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function listeMedecinsAction(Request $request)
    {
        $search = $request->query->get("recherche");
        if ($search == null) {
            $search = '%%';
        }

        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();
        $emMedecin = $em->getRepository(Medecin::class);
        $user = $this->getUser();

        $query = $emMedecin->getQuery($user, $search);

        $paginator = $this->get('knp_paginator');
        $medecins = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            20/*limit per page*/,
            array('defaultSortFieldName' => ['m.nom', 'm.prenom'], 'defaultSortDirection' => 'asc')
        );

        return $this->render('medecin/listeMedecins.html.twig', [
            'medecins' => $medecins
        ]);
    }

    /**
     * @Route("/medecins/advanced/recherche/{query}", name="searchMedecins", options={"expose"=true})
     * @param null $query
     * @return JsonResponse
     */
    public function searchMedecinsAction($query = null)
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $emMedecin = $em->getRepository(Medecin::class);
        $medecins = $emMedecin->findAdvancedArray($query, $user);

        return new JsonResponse($medecins);
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
        $emMedecin = $em->getRepository(Medecin::class);

        if ($truncate) {
            $em->createQuery('DELETE AppBundle:Medecin m')->execute();
        }

        $file = $this->get('kernel')->getRootDir() . '/../bdd/medecin.csv';
        $medecins = $csvToArray->convert($file, ";");

        $bulkSize = 500;
        $i = 0;
        foreach ($medecins as $m) {
            $i++;
            $medecin = false;

            // si le medecin n'a pas de nom et un prénom je l'ignore
            if (empty($m["Nom"]) || empty($m["Prénom"])) {
                continue;
            }

            foreach ($m as $k => $v) {
                $m[$k] = trim($v);
            }

            if ($checkIfExist) {
                $exist = $emMedecin->findOneBy(["nom" => $m["Nom"], "prenom" => $m["Prénom"]]);
                if ($exist) {
                    $medecin = $exist;
                }
            }

            if (!$medecin) {
                $medecin = new Medecin();
            }

            $dateEntre = \DateTime::createFromFormat('d/m/Y', $m["date d'entrée"]);
            $dateSortie = \DateTime::createFromFormat('d/m/Y', $m["Date départ"]);

            if (!$dateEntre) {
                $dateEntre = null;
            }

            if (!$dateSortie) {
                $dateSortie = null;
            }

            $medecin->setNom($m["Nom"]);
            $medecin->setPrenom($m["Prénom"]);
            $medecin->setDect($m["DECT"]);
            $medecin->setPortable($m["Téléphone"]);
            $medecin->setNote($m["Notes"]);
            $medecin->setSecNom($m["Nom secrétaire"]);
            $medecin->setSecTel($m["téléphone secrétariat"]);
            $medecin->setNumSiret($m["SIRET"]);
            $medecin->setNumSigaps($m["N°sigaps"]);
            $medecin->setNumOrdre($m["n°ORDRE"]);
            $medecin->setNumRpps($m["RPPS"]);
            $medecin->setEmail($m["Mail médecin"]);
            $medecin->setMerri($m["n°MERRI"]);
            $medecin->setDateEntre($dateEntre);
            $medecin->setDateSortie($dateSortie);

            if ($service = $em->getRepository(Service::class)->findOneBy(["nom" => $m["SERVICE"]])) {
                $medecin->setService($service);
            }

            $em->persist($medecin);

            if ($i % $bulkSize == 0) {
                $em->flush();
                $em->clear();
            }
        }

        $em->flush();
        $em->clear();

        return $this->redirectToRoute("listeMedecins");
    }
}
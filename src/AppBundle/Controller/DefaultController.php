<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Document;
use AppBundle\Entity\Medecin;
use AppBundle\Entity\Patient;
use AppBundle\Services\MoneticoService;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;

class DefaultController extends Controller
{

    /**
     * @Route("/mentions-legales", name="mentionsLegales")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function mentionsLegalesAction()
    {
        return $this->render('pages/mentions-legales.html.twig');
    }

    /**
     * @Route("/support", name="support")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function supportAction()
    {
        return $this->render('pages/support.html.twig');
    }

    /**
     * @Route("/create/custom/patient", name="createCustomPatient")
     * @return JsonResponse
     */
    public function generateCustomPatient()
    {
        set_time_limit(100);
        if ($this->container->getParameter('kernel.environment') != "demo") {
            return new JsonResponse(["Message" => "Désolé vous ne pouvez lancer cette commande quand environnement demo pour ne pas éffacer les patients de l'application."]);
        }

        $em = $this->getDoctrine()->getManager();

        $patients = $em->getRepository(Patient::class)->findAll();
        $jsonCustomPatient = json_decode(file_get_contents("https://randomuser.me/api/?results=1000&nat=Fr"), true);

        foreach ($patients as $k => $patient) {

            if($patient->getNom() == "TEST") {
                continue;
            };

            $patient->setNom($jsonCustomPatient["results"][$k]["name"]["last"]);
            $patient->setPrenom($jsonCustomPatient["results"][$k]["name"]["first"]);
            $patient->setdatNai(new \DateTime($jsonCustomPatient["results"][$k]["dob"]["date"]));
            $patient->setSexe(($jsonCustomPatient["results"][$k]["gender"] == "female") ? "F" : "H");
        }
        $em->flush();
        $em->clear();

        $medecins = $em->getRepository(Medecin::class)->findAll();
        $jsonCustomMedecin = json_decode(file_get_contents("https://randomuser.me/api/?results=1000&nat=Fr"), true);

        foreach ($medecins as $k => $medecin) {

            $medecin->setNom($jsonCustomMedecin["results"][$k]["name"]["last"]);
            $medecin->setPrenom($jsonCustomMedecin["results"][$k]["name"]["first"]);
            $medecin->setEmail($jsonCustomMedecin["results"][$k]["email"]);
            $medecin->setSecTel("");
            $medecin->setPortable("");
        }
        $em->flush();
        $em->clear();

        /** @var Document[] $documents */
        $documents = $em->getRepository(Document::class)->findAll();

        foreach ($documents as $k => $document) {

            if($document->getInclusion()->getPatient()->getNom() == "TEST") {
                continue;
            }
            $document->setFile(null);
        }
        $em->flush();
        $em->clear();

        $em->flush();
        return new JsonResponse(["Message" => "Les " . count($patients) . " utilisateurs ont étés randomisés"]);
    }


    /**
     * @Route("/vider/cache", name="viderCache")
     * @return JsonResponse
     */
    public function viderCache()
    {

        $process = new Process('chmod -R 777 ./../var/cache');
        $process->run();
        $process = new Process('php ./../bin/console cache:clear');
        $process->run();

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $message[] = $process->getOutput();

        $process = new Process('php ./../bin/console cache:clear --env=prod --no-debug');
        $process->run();

        // executes after the command finishes
        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        $message[] = $process->getOutput();

        $process = new Process('chmod -R 777 ./../var/cache');

        return new JsonResponse(["Message" => $message]);

    }

    /**
     * @Route("/import/all", name="importAll")
     */
    public function importAll()
    {
        $response = $this->forward('AppBundle\Controller\ServiceController::importAction', array());
        $response = $this->forward('AppBundle\Controller\ArcController::importAction', array());
        $response = $this->forward('AppBundle\Controller\MedecinController::importAction', array());
        $response = $this->forward('AppBundle\Controller\EssaisController::importAction', array());
        $response = $this->forward('AppBundle\Controller\PatientController::importAction', array());
        $response = $this->forward('AppBundle\Controller\InclusionController::importAction', array());
        $response = $this->forward('AppBundle\Controller\VisiteController::importAction', array());
        die();
    }

    /**
     * @Route("/monetico", name="monetico")
     * @param Request $request
     * @return Response
     */
    public function moneticoAction(Request $request)
    {
        $monetico = $this->get(MoneticoService::class);
        $form = $monetico->createForm("test2".uniqid(), 50, "EUR", "folken70@hotmail.com", "test");

        return $this->render('pages/mentions-legales.html.twig', [
           "form" => $form
        ]);
    }

}

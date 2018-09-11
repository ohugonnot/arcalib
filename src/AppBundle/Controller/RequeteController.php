<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Actualite;
use AppBundle\Entity\EI;
use AppBundle\Entity\Essais;
use AppBundle\Entity\Inclusion;
use AppBundle\Entity\Patient;
use AppBundle\Entity\Service;
use AppBundle\Entity\Visite;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;


/**
 * @Route("/arcalib")
 */
class RequeteController extends Controller
{
    // ------------------------------------------Requete recherche----------------------------------------------------- 
    /**
     * @Route("/recherche", name="recherche")
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     */
    public function requetesAction()
    {
        $em = $this->getDoctrine()->getManager();
        $emActualite = $em->getRepository(Actualite::class);
        $emService = $em->getRepository(Service::class);
        $emVisite = $em->getRepository(Visite::class);
        $emInclusion = $em->getRepository(Inclusion::class);
        $emEi = $em->getRepository(EI::class);
        $user = $this->getUser();

        $services = $emService->findAll();
        $actualites = $emActualite->findBy(["enabled" => true], ["date" => "desc"], 10);
        $visiteForWeek = $emVisite->findForAWeek($user);
        $eiAlertes = $emEi->findAlerteEi($user);
        $inclusionsScreen = $emInclusion->findByStatutScreen($user);

        $visiteByDay = [];
        foreach ($visiteForWeek as $key => $visite) {
            $visiteByDay[$visite->getDate()->format("Y-m-d")][] = $visite;
        }

        $today = (new \DateTime())->format('Y-m-d');
        if (!isset($visiteByDay[$today])) {
            $visiteByDay[$today] = [];
        }
        ksort($visiteByDay);

        return $this->render('recherche/recherche.html.twig', [
            "actualites" => $actualites,
            'todayVisites' => $visiteForWeek,
            'visiteForweek' => $visiteByDay,
            'services' => $services,
            'eiAlertes' => $eiAlertes,
            'inclusionsScreen' => $inclusionsScreen,
        ]);
    }

    // ------------------------------------------requete patients dont la visite est dans les 30 jours----------------------------------------------------- 

    /**
     * @Route("/requetes", name="requetes")
     * @return \Symfony\Component\HttpFoundation\Response
     */
    public function indexAction()
    {
        $em = $this->getDoctrine()->getManager();
        $user = $this->getUser();
        $emPatient = $em->getRepository(Patient::class);
        $emEssai = $em->getRepository(Essais::class);
        $emVisite = $em->getRepository(Visite::class);
        $patientLastNews = $emPatient->findPatientsDateNouvelle();
        $patientVisite30Days = $emPatient->findPatientVisite30Days();
        $essaiEnAttente = $emEssai->findEssaiEnAttente();
        $visiteConfirmeeTheorique = $emVisite->findConfirmeeTheoriqueDepassee($user);


        return $this->render('requetes/index.html.twig', [
            "patientLastNews" => $patientLastNews,
            "patientVisite30Days" => $patientVisite30Days,
            "essaiEnAttente" => $essaiEnAttente,
            "visiteConfirmeeTheorique" => $visiteConfirmeeTheorique,
        ]);
    }
}

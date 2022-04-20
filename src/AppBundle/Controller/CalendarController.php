<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Arc;
use AppBundle\Entity\Visite;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/arcalib/agenda")
 */
class CalendarController extends Controller
{

     /**
     * @Route("/", name="agenda")
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function indexAction(Request $request)
    {
        $arcs = $this->getDoctrine()->getManager()->getRepository(Arc::class)->findBy([], ["nomArc" => 'asc']);
        return $this->render('calendar/calendar.html.twig', [
            'arcs' => $arcs
        ]);
    }

    /**
     * @Route("/events", name="agenda_events", options={"expose"=true})
     * @param Request $request
     * @return JsonResponse
     */
    public function events(Request $request){
        $em = $this->getDoctrine()->getManager();

        $start_str = substr($request->get("start"), 0, 10);
        $end_str = substr($request->get("end"), 0, 10);

        $start = \DateTime::createFromFormat("Y-m-d", $start_str);
        $fin = \DateTime::createFromFormat("Y-m-d", $end_str);

        $user = $this->getUser();
        $emVisite = $em->getRepository(Visite::class);

        $visiteForWeek = $emVisite->findByDate($start, $fin, $user);

        $visiteByDay = [];
        foreach ($visiteForWeek as $visite) {
            /** @var Visite $visite */
            $patient = $visite->getInclusion()->getPatient();
            $dateStartVisite = $visite->getDate();
            $dateFinVisite = $visite->getDateFin();

            $data = [];

            $data["title"] = $patient->getPrenom()." ".$patient->getNom()." No inclusion : ".$visite->getInclusion()->getId();
            $data["start"] = $dateStartVisite->format("Y-m-d")."T".$dateStartVisite->format("H:i:s");
            if ($dateFinVisite){
                $data["end"] = $dateFinVisite->format("Y-m-d")."T".$dateFinVisite->format("H:i:s");
            }
            $data["color"] = "#FFF";
            $data["textColor"] = "blue";
            $data["statutEvt"] = $visite->getStatut();
            $data["noInclusion"] = $visite->getInclusion()->getId();
            $data["noVisite"] = $visite->getId();
            $data["idPatient"] = $patient->getId();

            $visiteByDay[] = $data;
        }

        return new JsonResponse($visiteByDay);
    }
}

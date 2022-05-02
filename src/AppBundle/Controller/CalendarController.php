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

        $start = \DateTime::createFromFormat("Y-m-d", $start_str)->setTime(0,0,0);
        $fin = \DateTime::createFromFormat("Y-m-d", $end_str)->setTime(0,0,0);

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
            $diff = 0;
            $timestampDebut = $dateStartVisite->getTimestamp();


            $data["title"] = "";
            $data["start"] = $dateStartVisite->format("Y-m-d")."T".$dateStartVisite->format("H:i:s");
            if ($dateFinVisite){
                $data["end"] = $dateFinVisite->format("Y-m-d")."T".$dateFinVisite->format("H:i:s");
                $data["dateEnd"] = $data["end"];
                $timestampFin = $dateFinVisite->getTimestamp();
                $diff = ($timestampFin - $timestampDebut)/(60*60);
            }
            $data["dateStart"] = $data["start"];

            $data["identitePatient"] = $patient->getPrenom()." ".$patient->getNom();
            $data["etude"] = $visite->getInclusion()->getEssai()->getNom();
            $data["typeVisite"] = $visite->getType();
            $data["color"] = "#FFF";
            $data["textColor"] = "blue";
            $data["statutEvt"] = $visite->getStatut();
            $data["noInclusion"] = $visite->getInclusion()->getId();
            $data["noVisite"] = $visite->getId();
            $data["note"] = $visite->getNote();
            $data["fait"] = $visite->getFait();
            $data["idPatient"] = $patient->getId();

            if($diff>12 || $visite->getAllDay())
                $data["allDay"] = true;

            $visiteByDay[] = $data;
        }

        return new JsonResponse($visiteByDay);
    }

    /**
     * @Route("/save_visit", name="agenda_save_visit", options={"expose"=true})
     * @param Request $request
     * @return JsonResponse
     */
    public function saveVisitAction(Request $request){
        $idVisite = $request->get("idVisite");
        $start = $request->get("start");
        $end = $request->get("end");

        $em = $this->getDoctrine()->getManager();
        /** @var Visite $visite */
        $visite = $em->getRepository(Visite::class)->find($idVisite);
        $visite->setDate(\DateTime::createFromFormat("Y-m-d H:i:s", $start));
        if (!empty($end)){
            $visite->setDateFin(\DateTime::createFromFormat("Y-m-d H:i:s", $end));
        }
        $em->persist($visite);
        $em->flush();
        return new JsonResponse();
    }
}

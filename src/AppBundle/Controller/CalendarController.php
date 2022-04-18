<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Visite;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Doctrine\ORM\EntityManager;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
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
        /** @var EntityManager $em */
        $em = $this->getDoctrine()->getManager();

        $user = $this->getUser();
        $emVisite = $em->getRepository(Visite::class);
        $visiteForWeek = $emVisite->findForAWeek($user);

        $visiteByDay = [];
        foreach ($visiteForWeek as $key => $visite) {
            $visiteByDay[$visite->getDate()->format("Y-m-d")][] = $visite;
        }

        return $this->render('calendar/calendar.html.twig',[
            'visiteForweek' => $visiteByDay
        ]);
    }
}

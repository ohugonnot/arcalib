<?php

namespace AppBundle\Controller;

use AppBundle\Entity\Log;
use AppBundle\Form\LogType;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * @Route("/arcalib")
 */
class LogController extends Controller
{

    // ------------------------------------------ADD LOG----------------------------------------------------- 
    /**
     * @Route("/log/ajouter", name="addLog")
     * @param Request $request
     * @return RedirectResponse|Response
     */
    public function addLogAction(Request $request)
    {
        $log = new Log();

        $form = $this->get('form.factory')->create(LogType::class, $log);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $em->persist($log);
            $em->flush();

            return $this->redirectToRoute("listeLogs");
        }

        return $this->render('log/addLog.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // ------------------------------------------Edit Log----------------------------------------------------- 

	/**
	 * @Route("/log/editer/{id}", name="editLog")
	 * @param Request $request
	 * @param Log $log
	 * @return RedirectResponse|Response
	 */
    public function editLogAction(Request $request, Log $log)
    {
        $em = $this->getDoctrine()->getManager();
        $form = $this->get('form.factory')->create(LogType::class, $log);

        if ($request->isMethod('POST') && $form->handleRequest($request)->isValid()) {
            $em->flush();

            return $this->redirectToRoute("listeLogs");
        }

        return $this->render('log/addLog.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    // ------------------------------------------Supp Log-----------------------------------------------------

	/**
	 * @Route("/log/supprimer/{id}", name="deleteLog", options={"expose"=true})
	 * @param Log $log
	 * @return RedirectResponse
	 */
    public function deleteLogAction(Log $log)
    {
        $em = $this->getDoctrine()->getManager();

        $em->remove($log);
        $em->flush();

        return $this->redirectToRoute("listeLogs");
    }

    // ------------------------------------------Liste Log-----------------------------------------------------

    /**
     * @Route("/logs/", name="listeLogs", options={"expose"=true})
     * @param Request $request
     * @return Response
     */
    public function listeLogsAction(Request $request)
    {
        $search = $request->query->get("recherche");
        if ($search == null) {
            $search = '%%';
        }

        $em = $this->getDoctrine()->getManager();
        $emLog = $em->getRepository(Log::class);

        $query = $emLog->createQueryBuilder('p')
            ->select('p')
            ->leftJoin('p.user', 'u')
            ->addSelect("u")
            ->where("(p.info like :search or p.entity like :search or p.action like :search or p.entityId like :search  or u.username like :search) and p.createdAt >= '" . date('Y-m-d', strtotime('-2 year')) . "'")
            ->groupBy('p.id')
            ->getQuery();

        $query->setParameters(array(
            'search' => '%' . $search . '%',
        ));

        $paginator = $this->get('knp_paginator');
        $logs = $paginator->paginate(
            $query, /* query NOT result */
            $request->query->getInt('page', 1)/*page number*/,
            150/*limit per page*/,
            array('defaultSortFieldName' => ['p.createdAt'], 'defaultSortDirection' => 'desc')
        );

        return $this->render('log/listeLogs.html.twig', [
            'logs' => $logs
        ]);
    }
}

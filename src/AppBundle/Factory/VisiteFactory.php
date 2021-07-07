<?php

namespace AppBundle\Factory;

use AppBundle\Entity\Arc;
use AppBundle\Entity\Inclusion;
use AppBundle\Entity\Visite;
use AppBundle\Form\VisiteType;
use DateTime;

class VisiteFactory implements FactoryInterface
{
	use Factory;

	/**
	 * @param $visite Visite
	 * @param array|null $params
	 * @return mixed
	 */
	public function hydrate($visite, ?array $params)
	{
		$request = $this->requestStack->getCurrentRequest();
		$form = $this->formFactory->create(VisiteType::class, $visite);
		$form->handleRequest($request);

		$inclusion_id = $request->request->get("inclusion");
		$inclusion = $this->entityManager->getRepository(Inclusion::class)->find($inclusion_id);
		$visite->setInclusion($inclusion);

		$arc = $this->entityManager->getRepository(Arc::class)->find($params["arc"]["id"]);
		$visite->setArc($arc);

		foreach ($params as $key => $value) {
			if (is_array($value) || $value == '') {
				unset($params[$key]);
			}
		}

		if (isset($params["date"])) {
			$date = DateTime::createFromFormat('d/m/Y H:i', $params["date"]);
			if($date)
			    $visite->setDate($date);
		}
        if (isset($params["date_fin"])) {
            $date_fin = DateTime::createFromFormat('d/m/Y H:i', $params["date_fin"]);
            if($date_fin)
                $visite->setDateFin($date_fin);
        }

		if (isset($params["fact"]) and $params["fact"] == "true") {
			$visite->setFact(true);
		} else {
			$visite->setFact(false);
		}

		$this->validate($visite);

		return $visite;
	}
}
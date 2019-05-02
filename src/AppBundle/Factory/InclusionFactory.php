<?php

namespace AppBundle\Factory;

use AppBundle\Entity\Inclusion;
use AppBundle\Form\InclusionType;

class InclusionFactory implements FactoryInterface
{

	use Factory;

	/**
	 * @param Inclusion $inclusion
	 * @param array|null $params
	 * @return Inclusion
	 */
	public function hydrate($inclusion, ?array $params = [])
	{
		$form = $this->formFactory->create(InclusionType::class, $inclusion);
		$form->handleRequest($this->requestStack->getCurrentRequest());

		if ($params["statut"] == "") {
			$params["statut"] = null;
		}
		if ($params["motifSortie"] == "") {
			$params["motifSortie"] = null;
		}

		$inclusion->setStatut($params["statut"]);
		$inclusion->setMotifSortie($params["motifSortie"]);

		if (isset($params["essai"]["id"])) {
			$essai = $this->entityManager->getRepository('AppBundle:Essais')->find($params["essai"]["id"]);
			$inclusion->setEssai($essai);
		}

		if (isset($params["medecin"]["id"])) {
			$medecin = $this->entityManager->getRepository('AppBundle:Medecin')->find($params["medecin"]["id"]);
			$inclusion->setMedecin($medecin);
		}

		if (isset($params["arc"]["id"])) {
			$arc = $this->entityManager->getRepository('AppBundle:Arc')->find($params["arc"]["id"]);
			$inclusion->setArc($arc);
		}

		if ($params["patient_id"] ?? false) {
			$patient = $this->entityManager->getRepository('AppBundle:Patient')->find($params["patient_id"]);
			$inclusion->setPatient($patient);
		}

		if (isset($params["service"]["id"])) {
			$service = $this->entityManager->getRepository('AppBundle:Service')->find($params["service"]["id"]);
			$inclusion->setService($service);
		}

		if (isset($params["booRa"]) && $params["booRa"] == "true") {
			$inclusion->setBooRa(true);
		} else {
			$inclusion->setBooRa(false);
		}

		if (isset($params["booBras"]) && $params["booBras"] == "true") {
			$inclusion->setBooBras(true);
		} else {
			$inclusion->setBooBras(false);
		}

		foreach ($params as $key => $value) {
			if (is_array($value) || $value == '') {
				unset($params[$key]);
			}
		}

		if (isset($params["datCst"])) {
			$datCst = \DateTime::createFromFormat('d/m/Y', $params["datCst"])->settime(0, 0);
			if ($datCst != $inclusion->getDatCst()) {
				$inclusion->setDatCst($datCst);
			}
		}
		if (isset($params["datScr"])) {
			$datScr = \DateTime::createFromFormat('d/m/Y', $params["datScr"])->settime(0, 0);
			if ($datScr != $inclusion->getDatScr()) {
				$inclusion->setDatScr($datScr);
			}
		}
		if (isset($params["datInc"])) {
			$datInc = \DateTime::createFromFormat('d/m/Y', $params["datInc"])->settime(0, 0);
			if ($datInc != $inclusion->getDatInc()) {
				$inclusion->setDatInc($datInc);
			}
		}
		if (isset($params["datRan"])) {
			$datRan = \DateTime::createFromFormat('d/m/Y', $params["datRan"])->settime(0, 0);
			if ($datRan != $inclusion->getDatRan()) {
				$inclusion->setDatRan($datRan);
			}
		}
		if (isset($params["datJ0"])) {
			$datJ0 = \DateTime::createFromFormat('d/m/Y', $params["datJ0"])->settime(0, 0);
			if ($datJ0 != $inclusion->getDatJ0()) {
				$inclusion->setDatJ0($datJ0);
			}
		}
		if (isset($params["datOut"])) {
			$datOut = \DateTime::createFromFormat('d/m/Y', $params["datOut"])->settime(0, 0);
			if ($datOut != $inclusion->getDatOut()) {
				$inclusion->setDatOut($datOut);
			}
		}

		$this->validate($inclusion);
		
		return $inclusion;
	}

	public function validate($inclusion)
	{
		$errors = $this->validator->validate($inclusion);

		if ($errors->count()) {
			$this->logger->error("Inclusion non cohérente", $this->validatorToArray->toArray($errors));
		}
	}
}
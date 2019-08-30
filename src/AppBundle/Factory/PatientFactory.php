<?php

namespace AppBundle\Factory;

use AppBundle\Entity\LibCim10;
use AppBundle\Entity\Medecin;
use AppBundle\Entity\Patient;
use AppBundle\Form\PatientType;
use DateTime;

class PatientFactory implements FactoryInterface
{
	use Factory;

	/**
	 * @param $patient Patient
	 * @param array|null $params
	 * @return mixed
	 */
	public function hydrate($patient, ?array $params)
	{
		$form = $this->formFactory->create(PatientType::class, $patient);
		$form->handleRequest($this->requestStack->getCurrentRequest());

		$l10 = $this->entityManager->getRepository(LibCim10::class)->find($params["libCim10"]["id"]);
		$patient->setLibCim10($l10);

		$medecin = $this->entityManager->getRepository(Medecin::class)->find($params["medecin"]["id"]);
		$patient->setMedecin($medecin);

		foreach ($params as $key => $value) {
			if (is_array($value) || $value == '') {
				unset($params[$key]);
			}
		}

		if (isset($params["datNai"])) {
			$datNai = DateTime::createFromFormat('d/m/Y', $params["datNai"])->settime(0,0);
			if($datNai != $patient->getDatNai()) {
				$patient->setDatNai($datNai);
			}
		}

		if (isset($params["datDiag"])) {
			$datDiag = DateTime::createFromFormat('d/m/Y', $params["datDiag"])->settime(0,0);
			if($datDiag != $patient->getDatDiag()) {
				$patient->setDatDiag($datDiag);
			}
		}
		if (isset($params["datLast"])) {
			$datLast = DateTime::createFromFormat('d/m/Y', $params["datLast"])->settime(0,0);
			if($datLast != $patient->getDatLast()) {
				$patient->setDatLast($datLast);
			}
		}
		if (isset($params["datDeces"])) {
			$datDeces = DateTime::createFromFormat('d/m/Y', $params["datDeces"])->settime(0,0);
			if($datDeces != $patient->getDatDeces()) {
				$patient->setDatDeces($datDeces);
			}
		}

		if (isset($params["cancer"]) and $params["cancer"] == "true") {
			$patient->setCancer(true);
		} else {
			$patient->setCancer(false);
		}

		$this->validate($patient);

		return $patient;
	}
}
<?php
namespace AppBundle\Import;

use AppBundle\Entity\Arc;
use AppBundle\Entity\Essais;
use AppBundle\Entity\Inclusion;
use AppBundle\Entity\Medecin;
use AppBundle\Entity\Patient;
use AppBundle\Entity\Service;

class InclusionImport implements ImportInterface
{
	use Import;

	public function import(bool $checkIfExist = true, bool $truncate = true): void
	{
		$emInclusion = $this->entityManager->getRepository(Inclusion::class);

		if ($truncate) {
			$this->entityManager->createQuery('DELETE AppBundle:Inclusion i')->execute();
		}

		$file = $this->kernel->getRootDir() . '/../bdd/inclusion.csv';
		$inclusions = $this->csvToArray->convert($file, ";");

		$bulkSize = 500;
		$i = 0;
		foreach ($inclusions as $inc) {
			$i++;
			$inclusion = false;

			// si une inclusion n'a pas un protocole et un patient je l'ignore
			if (empty($inc["Protocole"]) || empty($inc["Id Patient"])) {
				continue;
			}

			foreach ($inc as $k => $v) {
				$inc[$k] = trim($v);
			}

			$datScr = \DateTime::createFromFormat('d/m/Y', $inc["Date du screen"]);
			$datCst = \DateTime::createFromFormat('d/m/Y', $inc["Date du consentement"]);
			$datInc = \DateTime::createFromFormat('d/m/Y', $inc["Date d'inclusion"]);
			$datRan = \DateTime::createFromFormat('d/m/Y', $inc["Date de randomisation"]);
			$datJ0 = \DateTime::createFromFormat('d/m/Y', $inc["Date J0"]);
			$datOut = \DateTime::createFromFormat('d/m/Y', $inc["Date de sortie"]);

			$booRa = (strtolower($inc["Randomisation NA"]) == "vrai") ? true : false;

			if (!$datScr) {
				$datScr = null;
			}

			if (!$datCst) {
				$datCst = null;
			}

			if (!$datInc) {
				$datInc = null;
			}

			if (!$datRan) {
				$datRan = null;
			}

			if (!$datJ0) {
				$datJ0 = null;
			}

			if (!$datOut) {
				$datOut = null;
			}

			if ($checkIfExist) {
				$exist = $emInclusion->findOneBy(["idInterne" => $inc["N° inclusion table"]]);
				if ($exist) {
					$inclusion = $exist;
				}
			}

			if (!$inclusion) {
				$inclusion = new Inclusion();
			}

			$inclusion->setNumInc($inc["N° inclusion"]);
			$inclusion->setIdInterne($inc["N° inclusion table"]);
			$inclusion->setDatScr($datScr);
			$inclusion->setDatCst($datCst);
			$inclusion->setDatInc($datInc);
			$inclusion->setDatRan($datRan);
			$inclusion->setDatJ0($datJ0);
			$inclusion->setDatOut($datOut);
			$inclusion->setStatut($inc["Statut du patient"]);
			$inclusion->setBooRa($booRa);
			$inclusion->setBraTrt($inc["Bras de traitement"]);
			$inclusion->setMotifSortie($inc["Cause de sortie"]);

			if ($medecin = $this->entityManager->getRepository(Medecin::class)->findOneBy(["NomPrenomConcat" => $inc["Médecin responsable de l'Inclusion"]])) {
				$inclusion->setMedecin($medecin);
			}

			if ($patient = $this->entityManager->getRepository(Patient::class)->findOneBy(["idInterne" => $inc["Id Patient"]])) {
				$inclusion->setPatient($patient);
			}

			if ($essai = $this->entityManager->getRepository(Essais::class)->findOneBy(["nom" => $inc["Protocole"]])) {
				$inclusion->setEssai($essai);
			}

			if ($service = $this->entityManager->getRepository(Service::class)->findOneBy(["nom" => $inc["service"]])) {
				$inclusion->setService($service);
			}

			if ($arc = $this->entityManager->getRepository(Arc::class)->findOneBy(["iniArc" => $inc["ArcInc"]])) {
				$inclusion->setArc($arc);
			}

			$this->entityManager->persist($inclusion);

			if ($i % $bulkSize == 0) {
				$this->entityManager->flush();
				$this->entityManager->clear();
			}
		}

		$this->entityManager->flush();
		$this->entityManager->clear();
	}
}
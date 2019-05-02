<?php

namespace AppBundle\Import;

use AppBundle\Entity\LibCim10;
use AppBundle\Entity\Medecin;
use AppBundle\Entity\Patient;

class PatientImport implements ImportInterface
{
	use Import;

	/**
	 * @param bool $checkIfExist
	 * @param bool $truncate
	 * @throws \Doctrine\ORM\NonUniqueResultException
	 */
	public function import(bool $checkIfExist = true, bool $truncate = true): void
	{
		$emPatient = $this->entityManager->getRepository(Patient::class);

		if ($truncate) {
			$this->entityManager->createQuery('DELETE AppBundle:Patient p')->execute();
		}

		$file = $this->kernel->getRootDir() . '/../bdd/patient.csv';
		$patients = $this->csvToArray->convert($file, ";");

		$bulkSize = 500;
		$i = 0;
		foreach ($patients as $p) {
			$i++;
			$patient = false;

			// Si le patient n'a pas un nom et un prénom on l'ignore
			if (empty($p["NOM Patient"]) || empty($p["Prénom Patient"])) {
				continue;
			}

			foreach ($p as $k => $v) {
				$p[$k] = trim($v);
			}

			$datNai = \DateTime::createFromFormat('d/m/Y', $p["Date de naissance"]);
			$datDiag = \DateTime::createFromFormat('d/m/Y', $p["Date du diagnostic"]);
			$datLast = \DateTime::createFromFormat('d/m/Y', $p["Date dernières nouvelles"]);
			$datDeces = \DateTime::createFromFormat('d/m/Y', $p["Date  Décès"]);
			$cancer = (strtolower($p["Cancer O/N"]) == "vrai") ? true : false;;

			if (!$datNai) {
				$datNai = null;
			}

			if (!$datDiag) {
				$datDiag = null;
			}

			if (!$datLast) {
				$datLast = null;
			}

			if (!$datDeces) {
				$datDeces = null;
			}

			if ($checkIfExist) {
				$exist = $emPatient->findOneBy(["nom" => $p["NOM Patient"], "prenom" => $p["Prénom Patient"], "datNai" => $datNai]);
				if ($exist) {
					$patient = $exist;
				}
			}

			if (!$patient) {
				$patient = new Patient();
			}

			$patient->setIdInterne($p["Id Patient"]);
			$patient->setNom($p["NOM Patient"]);
			$patient->setPrenom($p["Prénom Patient"]);
			$patient->setDatNai($datNai);
			$patient->setDatDiag($datDiag);
			$patient->setDatLast($datLast);
			$patient->setDatDeces($datDeces);
			$patient->setNomNaissance($p["NOM JF"]);
			$patient->setIpp($p["IPP"]);
			$patient->setSexe($p["Sexe"]);
			$patient->setMemo($p["Notes Patient"]);
			$patient->setTxtDiag($p["Diagnostic"]);
			$patient->setCancer($cancer);
			$patient->setEvolution($p["Evolution"]);
			$patient->setDeces($p["Vivant ou décédé"]);

			if ($medecin = $this->entityManager->getRepository(Medecin::class)->findByNomPrenomConcat($p["Médecin référent"])) {
				$patient->setMedecin($medecin);
			}

			if ($libCim10 = $this->entityManager->getRepository(LibCim10::class)->findOneBy(["cim10code" => $p["Diagnostic CIM10"]])) {
				$patient->setLibCim10($libCim10);
			}

			$this->entityManager->persist($patient);;

			if ($i % $bulkSize == 0) {
				$this->entityManager->flush();
				$this->entityManager->clear();
			}
		}

		$this->entityManager->flush();
		$this->entityManager->clear();
	}
}
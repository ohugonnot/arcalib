<?php

namespace AppBundle\Import;

use AppBundle\Entity\Medecin;
use AppBundle\Entity\Service;

class MedecinImport implements ImportInterface
{
	use Import;

	public function import(bool $checkIfExist = true, bool $truncate = true): void
	{
		$emMedecin = $this->entityManager->getRepository(Medecin::class);

		if ($truncate) {
			$this->entityManager->createQuery('DELETE AppBundle:Medecin m')->execute();
		}

		$file = $this->get('kernel')->getRootDir() . '/../bdd/medecin.csv';
		$medecins = $this->csvToArray->convert($file, ";");

		$bulkSize = 500;
		$i = 0;
		foreach ($medecins as $m) {
			$i++;
			$medecin = false;

			// si le medecin n'a pas de nom et un prénom je l'ignore
			if (empty($m["Nom"]) || empty($m["Prénom"])) {
				continue;
			}

			foreach ($m as $k => $v) {
				$m[$k] = trim($v);
			}

			if ($checkIfExist) {
				$exist = $emMedecin->findOneBy(["nom" => $m["Nom"], "prenom" => $m["Prénom"]]);
				if ($exist) {
					$medecin = $exist;
				}
			}

			if (!$medecin) {
				$medecin = new Medecin();
			}

			$dateEntre = \DateTime::createFromFormat('d/m/Y', $m["date d'entrée"]);
			$dateSortie = \DateTime::createFromFormat('d/m/Y', $m["Date départ"]);

			if (!$dateEntre) {
				$dateEntre = null;
			}

			if (!$dateSortie) {
				$dateSortie = null;
			}

			$medecin->setNom($m["Nom"]);
			$medecin->setPrenom($m["Prénom"]);
			$medecin->setDect($m["DECT"]);
			$medecin->setPortable($m["Téléphone"]);
			$medecin->setNote($m["Notes"]);
			$medecin->setSecNom($m["Nom secrétaire"]);
			$medecin->setSecTel($m["téléphone secrétariat"]);
			$medecin->setNumSiret($m["SIRET"]);
			$medecin->setNumSigaps($m["N°sigaps"]);
			$medecin->setNumOrdre($m["n°ORDRE"]);
			$medecin->setNumRpps($m["RPPS"]);
			$medecin->setEmail($m["Mail médecin"]);
			$medecin->setMerri($m["n°MERRI"]);
			$medecin->setDateEntre($dateEntre);
			$medecin->setDateSortie($dateSortie);

			if ($service = $this->entityManager->getRepository(Service::class)->findOneBy(["nom" => $m["SERVICE"]])) {
				$medecin->setService($service);
			}

			$this->entityManager->persist($medecin);

			if ($i % $bulkSize == 0) {
				$this->entityManager->flush();
				$this->entityManager->clear();
			}
		}

		$this->entityManager->flush();
		$this->entityManager->clear();
	}
}
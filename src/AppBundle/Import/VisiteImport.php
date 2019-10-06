<?php

namespace AppBundle\Import;

use AppBundle\Entity\Arc;
use AppBundle\Entity\Inclusion;
use AppBundle\Entity\Visite;
use DateTime;

class VisiteImport implements ImportInterface
{
	use Import;

	public function import(bool $checkIfExist = true, bool $truncate = true): void
	{
		if ($truncate) {
			$this->entityManager->createQuery('DELETE AppBundle:Visite v')->execute();
		}

		$file = $this->kernel->getRootDir() . '/../bdd/visite.csv';
		$visites = $this->csvToArray->convert($file, ";");

		$bulkSize = 500;
		$i = 0;
		foreach ($visites as $p) {
			$i++;
			$visite = false;

			if (empty($p["N° inclusion table"]) || empty($p["Date visite"])) {
				continue;
			}

			foreach ($p as $k => $v) {
				$p[$k] = trim($v);
			}

			$date = DateTime::createFromFormat('d/m/Y', $p["Date visite"]);
			$fact = (strtolower($p["Facturé"]) == "vrai") ? true : false;;

			if (!$date) {
				$date = null;
			}

			if (!$visite) {
				$visite = new Visite();
			}

			$visite->setDate($date);
			$visite->setType($p["Type visite"]);
			$visite->setCalendar($p["JMA"]);
			$visite->setStatut($p["Statut visite"]);
			$visite->setNote($p["Notes"]);
			$visite->setFact($fact);
			$visite->setNumFact($p["N° facture"]);

			if ($inclusion = $this->entityManager->getRepository(Inclusion::class)->findOneBy(["idInterne" => $p["N° inclusion table"]])) {
				$visite->setInclusion($inclusion);
			}

			if ($arc = $this->entityManager->getRepository(Arc::class)->findOneBy(["nomArc" =>$p["Arc référent"]])) {
				$visite->setArc($arc);
			}

			$this->entityManager->persist($visite);;

			if ($i % $bulkSize == 0) {
				$this->entityManager->flush();
				$this->entityManager->clear();
			}
		}

		$this->entityManager->flush();
		$this->entityManager->clear();
	}
}
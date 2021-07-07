<?php

namespace AppBundle\Import;

use AppBundle\Entity\Arc;
use AppBundle\Entity\Service;
use DateTime;

class ArcImport implements ImportInterface
{

	use Import;

	/**
	 * @param bool $checkIfExist
	 * @param bool $truncate
	 */
	public function import($checkIfExist = true, $truncate = true): void
	{
		if ($truncate) {
			$this->entityManager->createQuery('DELETE AppBundle:Arc a')->execute();
		}

		$file = $this->kernel->getRootDir() . '/../bdd/arc.csv';
		$arcs = $this->csvToArray->convert($file, ";");

		$bulkSize = 500;
		$i = 0;
		foreach ($arcs as $a) {
			$i++;
			$arc = false;

			foreach ($a as $k => $v) {
				$a[$k] = trim($v);
			}

			if ($checkIfExist) {
				$exist = $this->entityManager->getRepository(Arc::class)->findOneBy(["nomArc" => $a["Nom ARC"],"prenomArc" => $a["Prénom ARC"]]);
				if ($exist) {
					$arc = $exist;
				}
			}

			if (!$arc) {
				$arc = new Arc();
			}

			$datIn = DateTime::createFromFormat('d/m/Y', $a["Date d'entrée"]);
			$datOut = DateTime::createFromFormat('d/m/Y', $a["Date de sortie"]);

			if (!$datIn) {
				$datIn = null;
			}

			if (!$datOut) {
				$datOut = null;
			}

			$arc->setNomArc($a["Nom ARC"]);
            $arc->setPrenomArc(($a["Prénom ARC"])??null);
			$arc->setDatIn($datIn);
			$arc->setDatOut($datOut);
			$arc->setIniArc($a["Initiales"]);
			$arc->setDect($a["n° Poste"]);
			$arc->setTel($a["Teléphone"]);
			$arc->setMail($a["Mail"]);

			if ($service = $this->entityManager->getRepository(Service::class)->findOneBy(["nom" => $a["SERVICE"]])) {
				$arc->setService($service);
			}

			$this->entityManager->persist($arc);

			if ($i % $bulkSize == 0) {
				$this->entityManager->flush();
				$this->entityManager->clear();
			}
		}

		$this->entityManager->flush();
		$this->entityManager->clear();
	}
}
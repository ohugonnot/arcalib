<?php

namespace AppBundle\Import;

use AppBundle\Entity\EssaiDetail;
use AppBundle\Entity\Essais;
use AppBundle\Entity\Medecin;

class EssaiImport implements ImportInterface
{

	use Import;

	/**
	 * @param bool $checkIfExist
	 * @param bool $truncate
	 */
	public function import($checkIfExist = true, $truncate = true): void
	{
		$em = $this->entityManager;
		$emEssai = $em->getRepository(Essais::class);

		if ($truncate) {
			$em->createQuery('DELETE AppBundle:Essais e')->execute();
		}

		$file = $this->kernel->getRootDir() . '/../bdd/essai.csv';
		$essais = $this->csvToArray->convert($file, ";");

		$bulkSize = 500;
		$i = 0;
		foreach ($essais as $e) {
			$i++;
			$essai = false;

			foreach ($e as $k => $v) {
				$e[$k] = trim($v);
			}

			if ($checkIfExist) {
				$exist = $emEssai->findOneBy(["nom" => $e["Nom de l'essai"]]);
				if ($exist) {
					$essai = $exist;
				}
			}

			if (!$essai) {
				$essai = new Essais();
			}

			$dateOuverture = \DateTime::createFromFormat('d/m/Y', $e["Date d'ouverture"]);
			$dateFin = \DateTime::createFromFormat('d/m/Y', $e["Fin des inclusions"]);
			$dateCloture = \DateTime::createFromFormat('d/m/Y', $e["Date Cloture centre"]);
			$sigrec = (strtolower($e["Sigrec"]) == "vrai") ? true : false;
			$sigaps = (strtolower($e["Sigaps"]) == "vrai") ? true : false;
			$emrc = (strtolower($e["Emrc"]) == "vrai") ? true : false;
			$cancer = (strtolower($e["Cancer"]) == "vrai") ? true : false;
			$dateSignature = \DateTime::createFromFormat('d/m/Y', $e["Date signature convention"]);
			$e["N° Eudract"] = ($e["N° Eudract"] == '') ? null : $e["N° Eudract"];
			$e["N° Clinical trial"] = ($e["N° Clinical trial"] == '') ? null : $e["N° Clinical trial"];

			if (!$dateOuverture) {
				$dateOuverture = null;
			}

			if (!$dateFin) {
				$dateFin = null;
			}

			if (!$dateCloture) {
				$dateCloture = null;
			}

			if (!$dateSignature) {
				$dateSignature = null;
			}

			if ($e["Nom de l'essai"] == '') {
				continue;
			}

			$essai->setNom($e["Nom de l'essai"]);
			$essai->setTitre($e["Tiitre de l'essai"]);
			$essai->setNumeroCentre($e["Numero centre"]);
			$essai->setDateOuv($dateOuverture);
			$essai->setDateFinInc($dateFin);
			$essai->setDateClose($dateCloture);
			$essai->setStatut($e["Statut essai"]);
			$essai->setTypeEssai($e["Type d'essai"]);
			$essai->setStadeEss($e["Phase"]);
			$essai->setProm($e["Promoteur"]);
			$essai->setTypeProm($e["Type de promoteur"]);
			$essai->setContactNom($e["Nom du contact"]);
			$essai->setContactTel($e["Tel du contact"]);
			$essai->setContactMail($e["Mail du contact"]);
			$essai->setEcrfLink($e["Lien Ecrf"]);
			$essai->setNotes($e["Remarque essai"]);
			$essai->setUrcGes($e["gestion par l'URC"]);
			$essai->setSigrec($sigrec);
			$essai->setSigaps($sigaps);
			$essai->setEmrc($emrc);
			$essai->setCancer($cancer);
			$essai->setTypeConv($e["Type Convention"]);
			$essai->setDateSignConv($dateSignature);
			$essai->setNumEudract($e["N° Eudract"]);
			$essai->setNumCt($e["N° Clinical trial"]);
			$essaiDetail = new EssaiDetail();
			$essai->setDetail($essaiDetail);

			if ($medecin = $em->getRepository(Medecin::class)->findOneBy(["nom" => $e["Médecin référent- NOM"], "prenom" => $e["Médecin référent-Prénom"]])) {
				$essai->setMedecin($medecin);
			}

			$em->persist($essai);
			$em->persist($essaiDetail);

			if ($i % $bulkSize == 0) {
				$em->flush();
				$em->clear();
			}
		}

		$em->flush();
		$em->clear();
	}
}
<?php

namespace AppBundle\Services;

use AppBundle\Entity\Document;
use AppBundle\Entity\Medecin;
use AppBundle\Entity\Patient;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\DependencyInjection\ContainerInterface;

class AnonymisationPatient
{
	private $entityManager;
	private $container;

	public function __construct(ContainerInterface $container, EntityManagerInterface $entityManager)
	{
		$this->entityManager = $entityManager;
		$this->container = $container;
	}

    /**
     * @return bool
     * @throws Exception
     */
    public function generateCustomPatient()
	{
		set_time_limit(100);
		if ($this->container->getParameter('kernel.environment') != "dev")
			return false;

		$patients = $this->entityManager->getRepository(Patient::class)->findAll();
		$jsonCustomPatient = json_decode(file_get_contents("https://randomuser.me/api/?results=1000&nat=Fr"), true);

		foreach ($patients as $k => $patient) {
			 if ($patient->getNom() == "TEST")
				continue;
			$patient->setNom($jsonCustomPatient["results"][$k]["name"]["last"]);
			$patient->setPrenom($jsonCustomPatient["results"][$k]["name"]["first"]);
			$patient->setdatNai(new DateTime($jsonCustomPatient["results"][$k]["dob"]["date"]));
			$patient->setSexe(($jsonCustomPatient["results"][$k]["gender"] == "female") ? "F" : "H");
		}
		$this->entityManager->flush();
		$this->entityManager->clear();

		$medecins = $this->entityManager->getRepository(Medecin::class)->findAll();
		$jsonCustomMedecin = json_decode(file_get_contents("https://randomuser.me/api/?results=1000&nat=Fr"), true);

		/**
		 * @var Medecin $medecin
		 */
		foreach ($medecins as $k => $medecin) {
			$medecin->setNom($jsonCustomMedecin["results"][$k]["name"]["last"]);
			$medecin->setPrenom($jsonCustomMedecin["results"][$k]["name"]["first"]);
			$medecin->setEmail($jsonCustomMedecin["results"][$k]["email"]);
			$medecin->setSecTel("");
			$medecin->setPortable("");
		}
		$this->entityManager->flush();
		$this->entityManager->clear();

		/** @var Document[] $documents */
		$documents = $this->entityManager->getRepository(Document::class)->findAll();
		foreach ($documents as $k => $document) {
			 if ($document->getInclusion()->getPatient()->getNom() == "TEST")
				continue;
			$document->setFile(null);
		}
		$this->entityManager->flush();
		$this->entityManager->clear();

		return true;
	}
}
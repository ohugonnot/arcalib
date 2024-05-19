<?php

namespace AppBundle\Import;

use AppBundle\Entity\LibCim10;
use AppBundle\Entity\Medecin;
use AppBundle\Entity\Patient;
use DateTime;
use Doctrine\ORM\NonUniqueResultException;

class PatientImport implements ImportInterface
{
    use Import;

    /**
     * @param bool $checkIfExist
     * @param bool $truncate
     * @throws NonUniqueResultException
     */
    public function import(bool $checkIfExist = true, bool $truncate = false): void
    {
        $emPatient = $this->entityManager->getRepository(Patient::class);

        if ($truncate) {
            $this->entityManager->createQuery('DELETE AppBundle:Patient p')->execute();
        }

        $file = $this->kernel->getRootDir() . '/../bdd/patient.csv';
        if (!file_exists($file)) {
            $file = $this->kernel->getRootDir() . '/../bdd/import.csv';
        }
        if (!file_exists($file)) {
            echo "Pas de patient a importé<br>";
            return;
        }
        $patients = $this->csvToArray->convert($file, ";");

        $bulkSize = 500;
        $i = 0;

        $patientsFinal = [];
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

            $datNai = DateTime::createFromFormat('d/m/Y', $p["Date de naissance"] ?? null);
            $datDiag = DateTime::createFromFormat('d/m/Y', $p["Date du diagnostic"] ?? null);
            $datLast = DateTime::createFromFormat('d/m/Y', $p["Date dernières nouvelles"] ?? null);
            $datDeces = DateTime::createFromFormat('d/m/Y', $p["Date  Décès"] ?? null);
            $cancer = (strtolower($p["Cancer O/N"] ?? null) == "vrai") ? true : false;;

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
            $idInterne = $p["Id Interne Patient"] ?? null;
            $idInterne = ($idInterne == "") ? null : $idInterne;
            $patient->setIdInterne($idInterne);
            $patient->setNom($p["NOM Patient"]);
            $patient->setPrenom($p["Prénom Patient"]);
            $patient->setDatNai($datNai);
            $patient->setDatDiag($datDiag);
            $patient->setDatLast($datLast);
            $patient->setDatDeces($datDeces);
            $patient->setNomNaissance($p["NOM JF"] ?? null);
            $patient->setIpp($p["IPP"] ?? null);
            $patient->setSexe($p["Sexe"] ?? null);
            $patient->setMemo($p["Notes Patient"] ?? null);
            $patient->setTxtDiag($p["Diagnostic"] ?? null);
            $patient->setCancer($cancer);
            $patient->setEvolution($p["Evolution"] ?? null);
            $patient->setDeces($p["Vivant ou décédé"] ?? null);

            if (!empty($p["Médecin référent"]) && $medecin = $this->entityManager->getRepository(Medecin::class)->findByNomPrenomConcat($p["Médecin référent"])) {
                $patient->setMedecin($medecin);
            }

            if (!empty($p["Diagnostic CIM10"]) && $libCim10 = $this->entityManager->getRepository(LibCim10::class)->findOneBy(["cim10code" => $p["Diagnostic CIM10"]])) {
                $patient->setLibCim10($libCim10);
            }

            $this->entityManager->persist($patient);

            if ($i % $bulkSize == 0) {
                $this->entityManager->flush();
                $this->entityManager->clear();
            }
            $patientsFinal[] = $patient;
        }

        $this->entityManager->flush();
        $this->entityManager->clear();
        dump($patientsFinal);
    }
}
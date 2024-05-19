<?php

namespace AppBundle\Import;

use AppBundle\Entity\Arc;
use AppBundle\Entity\Essais;
use AppBundle\Entity\Inclusion;
use AppBundle\Entity\Medecin;
use AppBundle\Entity\Patient;
use AppBundle\Entity\Service;
use DateTime;

class InclusionImport implements ImportInterface
{
    use Import;

    public function import(bool $checkIfExist = true, bool $truncate = false): void
    {
        $emInclusion = $this->entityManager->getRepository(Inclusion::class);

        if ($truncate) {
            $this->entityManager->createQuery('DELETE AppBundle:Inclusion i')->execute();
        }

        $file = $this->kernel->getRootDir() . '/../bdd/inclusion.csv';
        if (!file_exists($file)) {
            $file = $this->kernel->getRootDir() . '/../bdd/import.csv';
        }
        if (!file_exists($file)) {
            echo "Pas d'inclusion a importé<br>";
            return;
        }
        $inclusions = $this->csvToArray->convert($file, ";");

        $bulkSize = 500;
        $i = 0;
        $inclusionsFinal = [];
        foreach ($inclusions as $inc) {
            $i++;
            $inclusion = false;
            // si une inclusion n'a pas un protocole et un patient je l'ignore
            if (empty($inc["Protocole"])) {
                continue;
            }
            if (empty($inc["Id Patient"]) && empty($inc["Id Interne Patient"])) {
                if (!empty("NOM Patient") && !empty("Prénom Patient") && !empty("Date de naissance")) {
                    $datNai = DateTime::createFromFormat('d/m/Y', $inc["Date de naissance"] ?? null);
                    $emPatient = $this->entityManager->getRepository(Patient::class);
                    /** @var Patient $exist */
                    $exist = $emPatient->findOneBy(["nom" => $inc["NOM Patient"], "prenom" => $inc["Prénom Patient"], "datNai" => $datNai]);
                    if ($exist) {
                        $inc["Id Patient"] = $exist->getId();
                    }
                }
            }
            if (empty($inc["Id Patient"]) && empty($inc["Id Interne Patient"])) {
                continue;
            }
            foreach ($inc as $k => $v) {
                $inc[$k] = trim($v);
            }

            $datScr = DateTime::createFromFormat('d/m/Y', $inc["Date du screen"] ?? null);
            $datCst = DateTime::createFromFormat('d/m/Y', $inc["Date du consentement"] ?? null);
            $datInc = DateTime::createFromFormat('d/m/Y', $inc["Date d'inclusion"] ?? null);
            $datRan = DateTime::createFromFormat('d/m/Y', $inc["Date de randomisation"] ?? null);
            $datJ0 = DateTime::createFromFormat('d/m/Y', $inc["Date J0"] ?? null);
            $datOut = DateTime::createFromFormat('d/m/Y', $inc["Date de sortie"] ?? null);

            $booRa = (strtolower($inc["Randomisation NA"] ?? null) == "vrai") ? true : false;

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

            if ($checkIfExist && !empty($inc["N° inclusion table"])) {
                $exist = $emInclusion->findOneBy(["idInterne" => $inc["N° inclusion table"]]);
                if ($exist) {
                    $inclusion = $exist;
                }
            }

            if (!$inclusion) {
                $inclusion = new Inclusion();
            }

            $inclusion->setNumInc($inc["N° inclusion"] ?? null);
            $inclusion->setIdInterne($inc["N° inclusion table"] ?? null);
            $inclusion->setDatScr($datScr);
            $inclusion->setDatCst($datCst);
            $inclusion->setDatInc($datInc);
            $inclusion->setDatRan($datRan);
            $inclusion->setDatJ0($datJ0);
            $inclusion->setDatOut($datOut);
            $inclusion->setStatut($inc["Statut du patient"] ?? null);
            $inclusion->setBooRa($booRa);
            $inclusion->setBraTrt($inc["Bras de traitement"] ?? null);
            $inclusion->setMotifSortie($inc["Cause de sortie"] ?? null);

            /** @var Medecin $medecin */
            $csv_medecin = $inc["Médecin responsable de l'Inclusion"] ?? null;
            if ($csv_medecin) {
                if ($this->isIntString($csv_medecin)) {
                    if ($medecin = $this->entityManager->getRepository(Medecin::class)->find((int)$csv_medecin)) {
                        $inclusion->setMedecin($medecin);
                    }
                } else {
                    if ($medecin = $this->entityManager->getRepository(Medecin::class)->findOneBy(["NomPrenomConcat" => $csv_medecin])) {
                        $inclusion->setMedecin($medecin);
                    }
                }
            }

            /** @var Patient $patient */
            if (!empty($inc["Id Patient"] && $this->isIntString($inc["Id Patient"])) && $patient = $this->entityManager->getRepository(Patient::class)->find($inc["Id Patient"])) {
                $inclusion->setPatient($patient);
            }
            if (!empty($inc["Id Interne Patient"]) && $patient = $this->entityManager->getRepository(Patient::class)->findOneBy(["idInterne" => $inc["Id Interne Patient"]])) {
                $inclusion->setPatient($patient);
            }

            /** @var Essais $essai */
            if (!empty($inc["Protocole"])) {
                if ($this->isIntString($inc["Protocole"])) {
                    if ($essai = $this->entityManager->getRepository(Essais::class)->find((int)$inc["Protocole"])) {
                        $inclusion->setEssai($essai);
                    }
                } else {
                    if ($essai = $this->entityManager->getRepository(Essais::class)->findOneBy(["nom" => $inc["Protocole"]])) {
                        $inclusion->setEssai($essai);
                    }
                }
            }

            if (!empty($inc["Service"])) {
                if ($this->isIntString($inc["Service"])) {
                    if ($service = $this->entityManager->getRepository(Service::class)->find((int)$inc["Service"])) {
                        $inclusion->setService($service);
                    }
                } else {
                    /** @var Service $service */
                    if ($service = $this->entityManager->getRepository(Service::class)->findOneBy(["nom" => $inc["Service"]])) {
                        $inclusion->setService($service);
                    }
                }
            }

            if (!empty($inc["ArcInc"])) {
                if ($this->isIntString($inc["ArcInc"])) {
                    /** @var Arc $arc */
                    if ($arc = $this->entityManager->getRepository(Arc::class)->find($inc["ArcInc"])) {
                        $inclusion->setArc($arc);
                    }
                } else {
                    /** @var Arc $arc */
                    if ($arc = $this->entityManager->getRepository(Arc::class)->findOneBy(["iniArc" => $inc["ArcInc"]])) {
                        $inclusion->setArc($arc);
                    }
                }

            }

            $this->entityManager->persist($inclusion);

            if ($i % $bulkSize == 0) {
                $this->entityManager->flush();
                $this->entityManager->clear();
            }
            $inclusionsFinal[] = $inclusion;
        }

        $this->entityManager->flush();
        $this->entityManager->clear();
        dump($inclusionsFinal);
    }

    private function isIntString($str)
    {
        // Check if the string is numeric
        if (is_numeric($str)) {
            // Convert the string to an integer
            $intVal = (int)$str;
            // Convert the integer back to a string
            $strVal = (string)$intVal;
            // Check if the original string and the converted string are the same
            return $str === $strVal;
        }
        return false;
    }
}

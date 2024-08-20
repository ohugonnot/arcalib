<?php

namespace AppBundle\Services;

use AppBundle\Entity\Essais;
use AppBundle\Entity\Facture;
use AppBundle\Entity\Inclusion;
use AppBundle\Entity\Patient;
use AppBundle\Entity\Visite;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CsvToArray
{
    protected $cols = [];
    protected $name;
    protected $titles = [];
    protected $em;

    /**
     * CsvToArray constructor.
     * @param $em
     */
    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function convert($filename, $delimiter = ';')
    {
        if (!file_exists($filename) || !is_readable($filename))
            return FALSE;

        $header = NULL;
        $data = array();

        if (($handle = fopen($filename, 'r')) !== FALSE) {
            while (($row = fgetcsv($handle, 1000000, $delimiter)) !== FALSE) {
                if (!$header)
                    $header = $row;
                else
                    $data[] = array_combine($header, $row);
            }
            fclose($handle);
        }

        return $data;
    }

    public function exportCSV($listes, $name, $columns = [])
    {
        $this->name = $name;
        $this->titles = $this->getEntityColumn($listes[0]);
        if (!empty($columns)) {
            foreach ($this->titles as $k => $v) {
                $columns = array_map("strtolower", $columns);
                if (!in_array(strtolower($k), $columns)) {
                    unset($this->titles[$k]);
                }
            }
        }

        $response = new StreamedResponse();
        $response->setCallback(function () use ($listes, $columns) {
            $handle = fopen('php://output', 'w+');
            fputcsv($handle, $this->titles, ';');
            foreach ($listes as $liste) {
                $liste = $this->getEntityColumnValues($liste);
                if (!empty($columns)) {
                    foreach ($liste as $k => $v) {
                        $columns = array_map("strtolower", $columns);
                        if (!in_array(strtolower($k), $columns)) {
                            unset($liste[$k]);
                        }
                    }
                }
                fputcsv(
                    $handle,
                    $liste,
                    ';'
                );
            }
            fclose($handle);
        });
        $response->setStatusCode(200);
        $response->headers->set('Content-Type', 'text/csv; charset=utf-8');
        $response->headers->set('Content-Disposition', 'attachment; filename="export-' . $name . '-' . date('m-d-Y_hia') . '.csv"');
        echo "\xEF\xBB\xBF";

        return $response;
    }

    function getEntityColumn($entity, $name = null)
    {
        $this->cols = $this->em->getClassMetadata(get_class($entity))->getColumnNames();
        foreach ($this->cols as $col) {
            if (in_array($col, ["synopsis", "protocole", "crf", "nip", "procedure"]))
                continue;
            $values[$col] = $col;
        }

        $fonction = $this->name . 'Titles';
        if ($name)
            $fonction = $name . 'Titles';

        if (method_exists($this, $fonction))
            $values = $this->$fonction($values ?? [], $entity);

        return $values ?? [];
    }

    function getEntityColumnValues($entity, $name = null)
    {
        $this->cols = $this->em->getClassMetadata(get_class($entity))->getColumnNames();
        foreach ($this->cols as $col) {
            $getter = 'get' . ucfirst($col);
            if (in_array($col, ["synopsis", "protocole", "crf", "nip", "procedure"]))
                continue;
            if (!method_exists($entity, $getter)) {
                $getter = $this->camelize($getter);
            }
            if (!method_exists($entity, $getter))
                continue;
            if (is_a($entity->$getter(), 'DateTime'))
                $values[$col] = $entity->$getter()->format('d/m/Y');
            elseif ($col == 'NumInc')
                $values[$col] = '="' . $entity->$getter() . '"';
            else
                $values[$col] = $entity->$getter();
        }

        $fonction = $this->name;
        if ($name)
            $fonction = $name;

        if (method_exists($this, $fonction))
            $values = $this->$fonction($values ?? [], $entity);

        return $values ?? [];
    }

    function camelize($input, $separator = '_')
    {
        return str_replace($separator, '', ucwords($input, $separator));
    }

    //extractions  de la page inclusion

    /**
     * TODO : duplicate code content
     * @param $values
     * @param $entity
     * @return array
     */
    public function inclusions($values, $entity)
    {
        /** @var $entity Inclusion */
        $medecinRef = ($entity->getMedecin() != null) ? $entity->getMedecin()->getNom() . ' ' . $entity->getMedecin()->getPrenom() : "";
        $service = ($entity->getService() != null) ? $entity->getService()->getNom() : "";
        $patientNomPrenom = ($entity->getPatient() != null) ? $entity->getPatient()->getNom() . ' ' . $entity->getPatient()->getPrenom() : "";
        $patientInitial = ($entity->getPatient() != null) ? $entity->getPatient()->initial() : "";
        $essaiNom = ($entity->getEssai() != null) ? $entity->getEssai()->getNom() : "";

        return array_merge(["patient" => $patientNomPrenom, "initial" => $patientInitial, "protocole" => $essaiNom, "medecin" => $medecinRef, "service" => $service], $values);
    }

    public function inclusionsTitles($values)
    {
        return array_merge(["patient" => "Patient", "initial" => "Initiales", "protocole" => "Protocole Nom", "medecin" => "Médecin référent", "service" => "Service"], $values);
    }

    //extractions  de la page patient
    public function patients($values, $entity)
    {
        /** @var $entity Patient */
        $libCim10 = ($entity->getLibCim10() != null) ? $entity->getLibCim10()->getcim10code() : "";

        return array_merge($values, ["libCim10" => $libCim10]);
    }

    public function patientsTitles($values)
    {
        return array_merge($values, ["libCim10" => "Code CIM10"]);
    }

    //extractions  de la page Essais
    public function essais($values, $entity)
    {
        /** @var $entity Essais */
        $medecinRef = ($entity->getMedecin() != null) ? $entity->getMedecin()->getNom() . ' ' . $entity->getMedecin()->getPrenom() : "";
        $arc = ($entity->getArc() != null) ? $entity->getArc()->getNomArc() . ' ' . $entity->getArc()->getPrenomArc() : "";
        $arcBackup = ($entity->getArcBackup() != null) ? $entity->getArcBackup()->getNomArc() . ' ' . $entity->getArcBackup()->getPrenomArc() : "";
        return array_merge($values, ["medecin" => $medecinRef, "arc" => $arc, "arcBackup" => $arcBackup]);
    }

    public function essaistitles($values)
    {
        return array_merge($values, ["medecin" => "Medecin référent", "arc" => "Arc", "arcBackup" => "Arc Backup"]);
    }

    //extractions  des factures
    public function factures($values, $entity)
    {
        /** @var $entity Facture */
        $essaiNom = ($entity->getEssai() != null) ? $entity->getEssai()->getNom() : "";

        return array_merge($values, ["protocole" => $essaiNom]);
    }

    public function facturestitles($values)
    {
        return array_merge($values, ["protocole" => "Protocole"]);
    }

    //extractions  des factures

    /**
     * TODO : duplicate code content
     * @param $values
     * @param $entity
     * @return array
     */
    public function inclusionsProtocole($values, $entity)
    {
        /** @var $entity Inclusion */
        $medecinRef = ($entity->getMedecin() != null) ? $entity->getMedecin()->getNom() . ' ' . $entity->getMedecin()->getPrenom() : "";
        $service = ($entity->getService() != null) ? $entity->getService()->getNom() : "";
        $patientNomPrenom = ($entity->getPatient() != null) ? $entity->getPatient()->getNom() . ' ' . $entity->getPatient()->getPrenom() : "";
        $patientInitial = ($entity->getPatient() != null) ? $entity->getPatient()->initial() : "";
        if ($entity->getEssai() == null) {
            $entity->setEssai(new Essais());
        }
        return array_merge(["patient" => $patientNomPrenom, "initial" => $patientInitial, "medecin" => $medecinRef, "service" => $service], $values, $this->getEntityColumnValues($entity->getEssai(), "essais"));
    }

    public function inclusionsProtocoleTitles($values, $entity)
    {
        /** @var $entity Inclusion */
        if ($entity->getEssai() == null) {
            $entity->setEssai(new Essais());
        }
        return array_merge(["patient" => "Patient", "initial" => "Initiales", "medecin" => "Médecin référent de l'inclusion", "service" => "Service"], $values, $this->getEntityColumn($entity->getEssai(), "essais"));
    }

    //extractions  des factures
    public function visites($values, $entity)
    {
        /** @var $entity Visite */
        $inclusion = $entity->getInclusion();
        $essai = $inclusion ? $inclusion->getEssai() : null;
        $essaiNom = $essai ? $essai->getNom() : null;

        $arc = $entity->getArc();
        $nomPrenomArc = $arc ? $arc->getNomPrenom() : null;

        return array_merge($values, ["protocole" => $essaiNom, "arc" => $nomPrenomArc]);
    }

    public function visitesTitles($values)
    {
        return array_merge($values, ["protocole" => "Protocole", "arc" => "ARC"]);
    }

}
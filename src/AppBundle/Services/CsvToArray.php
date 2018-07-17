<?php

namespace AppBundle\Services;

use AppBundle\Entity\Essais;
use AppBundle\Entity\Facture;
use AppBundle\Entity\Inclusion;
use AppBundle\Entity\Patient;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\StreamedResponse;

class CsvToArray
{

    protected $cols = [];
    protected $name;
    protected $titles;
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
        if (!file_exists($filename) || !is_readable($filename)) {
            return FALSE;
        }

        $header = NULL;
        $data = array();

        if (($handle = fopen($filename, 'r')) !== FALSE) {
            while (($row = fgetcsv($handle, 1000000, $delimiter)) !== FALSE) {
                if (!$header) {
                    $header = $row;
                } else {
                    $data[] = array_combine($header, $row);
                }
            }
            fclose($handle);
        }

        return $data;
    }

    public function exportCSV($listes, $name)
    {
        $this->name = $name;
        $this->titles = $this->getEntityColumn($listes[0]);


        $response = new StreamedResponse();
        $response->setCallback(function () use ($listes) {
            $handle = fopen('php://output', 'w+');

            fputcsv($handle, $this->titles, ';');

            foreach ($listes as $liste) {
                $liste = $this->getEntityColumnValues($liste);
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

        $values = array();

        foreach ($this->cols as $col) {
            if (in_array($col, ["synopsis", "protocole", "crf", "nip", "procedure"])) {
                continue;
            }
            $values[] = $col;
        }

        $fonction = $this->name . 'Titles';
        if ($name) {
            $fonction = $name . 'Titles';
        }

        if (method_exists($this, $fonction)) {
            $values = $this->$fonction($values, $entity);
        }

        return $values;
    }

    function getEntityColumnValues($entity, $name = null)
    {

        $this->cols = $this->em->getClassMetadata(get_class($entity))->getColumnNames();

        $values = array();

        foreach ($this->cols as $col) {
            $getter = 'get' . ucfirst($col);
            if (in_array($col, ["synopsis", "protocole", "crf", "nip", "procedure"])) {
                continue;
            }

            if (is_a($entity->$getter(), 'DateTime')) {
                $values[] = $entity->$getter()->format('d/m/Y');
            } elseif ($col == 'NumInc') {
                $values[] = '="' . $entity->$getter() . '"';
            } else {
                $values[] = $entity->$getter();
            }
        }

        $fonction = $this->name;
        if ($name) {
            $fonction = $name;
        }

        if (method_exists($this, $fonction)) {
            $values = $this->$fonction($values, $entity);
        }

        return $values;
    }

    //extractions  de la page inclusion

    public function inclusions($values, $entity)
    {
        /** @var $entity Inclusion */
        $medecinRef = ($entity->getMedecin() != null) ? $entity->getMedecin()->getNom() . ' ' . $entity->getMedecin()->getPrenom() : "";
        $service = ($entity->getService() != null) ? $entity->getService()->getNom() : "";
        $patientNomPrenom = ($entity->getPatient() != null) ? $entity->getPatient()->getNom() . ' ' . $entity->getPatient()->getPrenom() : "";
        $patientInitial = ($entity->getPatient() != null) ? $entity->getPatient()->initial() : "";
        $essaiNom = ($entity->getEssai() != null) ? $entity->getEssai()->getNom() : "";

        return array_merge([$patientNomPrenom, $patientInitial, $essaiNom, $medecinRef, $service], $values);
    }


    public function inclusionsTitles($values)
    {

        return array_merge(["Patient", "Initiales", "Protocole", "Médecin référent", "Service"], $values);
    }

    //extractions  de la page patient
    public function patients($values, $entity)
    {
        /** @var $entity Patient */
        $libCim10 = ($entity->getLibCim10() != null) ? $entity->getLibCim10()->getcim10code() : "";

        return array_merge($values, [$libCim10]);
    }


    public function patientsTitles($values)
    {

        return array_merge($values, ["Code CIM10"]);
    }


    //extractions  de la page Essais
    public function essais($values, $entity)
    {
        /** @var $entity Essais */
        $medecinRef = ($entity->getMedecin() != null) ? $entity->getMedecin()->getNom() . ' ' . $entity->getMedecin()->getPrenom() : "";

        return array_merge($values, [$medecinRef]);
    }

    public function essaistitles($values)
    {

        return array_merge($values, ["Medecin référent"]);
    }

    //extractions  des factures
    public function factures($values, $entity)
    {
        /** @var $entity Facture */
        $essaiNom = ($entity->getEssai() != null) ? $entity->getEssai()->getNom() : "";

        return array_merge($values, [$essaiNom]);
    }

    public function facturestitles($values)
    {

        return array_merge($values, ["Protocole"]);
    }

    //extractions  des factures
    public function inclusionsProtocole($values, $entity)
    {
        /** @var $entity Inclusion */
        $medecinRef = ($entity->getMedecin() != null) ? $entity->getMedecin()->getNom() . ' ' . $entity->getMedecin()->getPrenom() : "";
        $service = ($entity->getService() != null) ? $entity->getService()->getNom() : "";
        $patientNomPrenom = ($entity->getPatient() != null) ? $entity->getPatient()->getNom() . ' ' . $entity->getPatient()->getPrenom() : "";
        $patientInitial = ($entity->getPatient() != null) ? $entity->getPatient()->initial() : "";

        return array_merge([$patientNomPrenom, $patientInitial, $medecinRef, $service], $values, $this->getEntityColumnValues($entity->getEssai(), "essais"));
    }

    public function inclusionsProtocoleTitles($values, $entity)
    {
        /** @var $entity Inclusion */
        return array_merge(["Patient", "Initiales", "Médecin référent de l'inclusion", "Service"], $values, $this->getEntityColumn($entity->getEssai(), "essais"));
    }
}
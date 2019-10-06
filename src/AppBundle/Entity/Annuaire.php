<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * Annuaire
 *
 * @ORM\Table(name="annuaire")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\AnnuaireRepository")
 */
class Annuaire
{

    const PHARMACIEN = "Pharmacien";
    const MEDECIN = 'Medecin';
    const ARC_H = 'ARC H';
    const ARC_M = 'ARC M';
    const SECRETAIRE = 'Secretaire';
    const DELEGUE = 'Délégué';
    const FOURNISSEUR = 'Fournisseur';
    const ADMNISTRATIF = 'Administratif';
    const COMPTABILITE = 'Comptabilité';
    const REFERENT_SCIENTIFIQUE = 'Référent scientifique';
    const RH = 'RH';
    const DATAM = 'DataM';
    const JURISTE = 'Juriste';
    const IDE = 'IDE';
    const KINE = 'Kiné';
    const CHEF_PROJET = 'Chef projet';
    const AUTRE = 'Autre';

    CONST FONCTION = [
        "Pharmacien" => self::PHARMACIEN,
        "Medecin" => self::MEDECIN,
        "ARC H" => self::ARC_H,
        "ARC M" => self::ARC_M,
        "Secretaire" => self::SECRETAIRE,
        "Délégué" => self::DELEGUE,
        "Fournisseur" => self::FOURNISSEUR,
        "Administratif" => self::ADMNISTRATIF,
        "Comptabilité" => self::COMPTABILITE,
        "Référent scientifique" => self::REFERENT_SCIENTIFIQUE,
        "RH" => self::RH,
        "DataM" => self::DATAM,
        "Juriste" => self::JURISTE,
        "IDE" => self::IDE,
        "Kiné" => self::KINE,
        "Chef projet" => self::CHEF_PROJET,
        "Autre" => self::AUTRE,
    ];

    /**
     * @var int
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="nom", type="string", length=255)
     */
    private $nom;

    /**
     * @var string
     * @ORM\Column(name="prenom", type="string", length=255, nullable=true)
     */
    private $prenom;

    /**
     * @var string
     * @ORM\Column(name="fonction", type="string", length=255, nullable=true)
     */
    private $fonction;

    /**
     * @var string
     * @ORM\Column(name="societe", type="string", length=255, nullable=true)
     */
    private $societe;

    /**
     * @var string
     * @ORM\Column(name="mail", type="string", length=255, nullable=true)
     */
    private $mail;

    /**
     * @var string
     * @ORM\Column(name="telephone", type="string", length=50, nullable=true)
     */
    private $telephone;

    /**
     * @var string
     * @ORM\Column(name="portable", type="string", length=50, nullable=true)
     */
    private $portable;

    /**
     * @var string
     * @ORM\Column(name="fax", type="string", length=50, nullable=true)
     */
    private $fax;

    /**
     * @var string
     * @ORM\Column(name="notes", type="text", nullable=true)
     */
    private $notes;

    /**
     * @var string
     * @ORM\Column(name="autre", type="string", length=255, nullable=true)
     */
    private $autre;

    /**
     * @ORM\ManyToOne(targetEntity="Essais", inversedBy="annuaires")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $essai;

    /**
     * Get id
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get nom
     * @return string
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Set nom
     * @param string $nom
     * @return Annuaire
     */
    public function setNom($nom)
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get prenom
     * @return string
     */
    public function getPrenom()
    {
        return $this->prenom;
    }

    /**
     * Set prenom
     * @param string $prenom
     * @return Annuaire
     */
    public function setPrenom($prenom)
    {
        $this->prenom = $prenom;

        return $this;
    }

    /**
     * Get fonction
     * @return string
     */
    public function getFonction()
    {
        return $this->fonction;
    }

    /**
     * Set fonction
     * @param string $fonction
     * @return Annuaire
     */
    public function setFonction($fonction)
    {
        $this->fonction = $fonction;

        return $this;
    }

    /**
     * Get societe
     * @return string
     */
    public function getSociete()
    {
        return $this->societe;
    }

    /**
     * Set societe
     * @param string $societe
     * @return Annuaire
     */
    public function setSociete($societe)
    {
        $this->societe = $societe;

        return $this;
    }

    /**
     * Get mail
     * @return string
     */
    public function getMail()
    {
        return $this->mail;
    }

    /**
     * Set mail
     * @param string $mail
     * @return Annuaire
     */
    public function setMail($mail)
    {
        $this->mail = $mail;

        return $this;
    }

    /**
     * Get telephone
     * @return string
     */
    public function getTelephone()
    {
        return $this->telephone;
    }

    /**
     * Set telephone
     * @param string $telephone
     * @return Annuaire
     */
    public function setTelephone($telephone)
    {
        $this->telephone = $telephone;

        return $this;
    }

    /**
     * Get portable
     * @return string
     */
    public function getPortable()
    {
        return $this->portable;
    }

    /**
     * Set portable
     * @param string $portable
     * @return Annuaire
     */
    public function setPortable($portable)
    {
        $this->portable = $portable;

        return $this;
    }

    /**
     * Get fax
     * @return string
     */
    public function getFax()
    {
        return $this->fax;
    }

    /**
     * Set fax
     * @param string $fax
     * @return Annuaire
     */
    public function setFax($fax)
    {
        $this->fax = $fax;

        return $this;
    }

    /**
     * Get notes
     * @return string
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * Set notes
     * @param string $notes
     * @return Annuaire
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * Get essai
     * @return Essais
     */
    public function getEssai()
    {
        return $this->essai;
    }

    /**
     * Set essai
     * @param Essais $essai
     * @return Annuaire
     */
    public function setEssai(Essais $essai = null)
    {
        $this->essai = $essai;

        return $this;
    }

    /**
     * Get autre
     * @return string
     */
    public function getAutre()
    {
        return $this->autre;
    }

    /**
     * Set autre
     * @param string $autre
     * @return Annuaire
     */
    public function setAutre($autre)
    {
        $this->autre = $autre;

        return $this;
    }
}

<?php

namespace AppBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Patient
 *
 * @ORM\Table(name="patient")
 * @UniqueEntity(
 *     fields={"nom", "prenom", "datNai"},
 *     message="Ce patient existe déjà !"
 * )
 * @ORM\Entity(repositoryClass="AppBundle\Repository\PatientRepository")
 */
class Patient
{

    const MASCULIN = "H";
    const FEMININ = 'F';

    const SEXE = [
        'Masculin' => self::MASCULIN,
        'Féminin' => self::FEMININ
    ];

    const GUERISON = "Guérison";
    const RC = 'RC';
    const RP = "RP";
    const STABLE = 'Stable';
    const PROGRESSION = "Progression";
    const RECIDIVE = 'Récidive';
    const PALIATIF = "Palliatif";
    const EN_COURS = 'En cours';
    const UNK = "Unk";
    const NA = 'NA';

    const EVOLUTION = [
        'Guérison' => self::GUERISON,
        'RC' => self::RC,
        'RP' => self::RP,
        'Stable' => self::STABLE,
        'Progression' => self::PROGRESSION,
        'Récidive' => self::RECIDIVE,
        'Palliatif' => self::PALIATIF,
        'En cours' => self::EN_COURS,
        'Inconnu' => self::UNK,
        'NA' => self::NA,
    ];

    const VIVANT = "Vivant";
    const DECEDE = 'Décédé';
    const NSP = "NSP";

    const DECES = [
        'Vivant' => self::VIVANT,
        'Décédé' => self::DECEDE,
        'NSP' => self::NSP,
    ];

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var int
     *
     * @ORM\Column(name="idInterne", type="integer", unique=true, nullable=true)
     */
    private $idInterne;

    /**
     * @var string
     *
     * @ORM\Column(name="Nom", type="string", length=100)
     */
    private $nom;

    /**
     * @var string
     *
     * @ORM\Column(name="nomNaissance", type="string", length=100, nullable=true)
     */
    private $nomNaissance;

    /**
     * @var string
     *
     * @ORM\Column(name="Prenom", type="string", length=100)
     */
    private $prenom;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="DatNai", type="date")
     */
    private $datNai;


    /**
     * @var string
     *
     * @ORM\Column(name="Sexe", type="string", length=1, nullable=true)
     */
    private $sexe;


    /**
     * @var string
     *
     * @ORM\Column(name="IPP", type="string", length=50, nullable=true)
     */
    private $ipp;


    /**
     * @var DateTime
     *
     * @ORM\Column(name="DatDiag", type="date", nullable=true)
     */
    private $datDiag;

    /**
     * @var string
     *
     * @ORM\Column(name="TxtDiag", type="text", nullable=true)
     */
    private $txtDiag;

    /**
     * @var string
     *
     * @ORM\Column(name="cancer", type="boolean", nullable=true)
     */
    private $cancer;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="DatLast", type="date", nullable=true)
     */
    private $datLast;


    /**
     * @var string
     *
     * @ORM\Column(name="Evolution", type="string", length=50, nullable=true)
     */
    private $evolution;


    /**
     * @var string
     *
     * @ORM\Column(name="Deces", type="string", length=10, nullable=true)
     */
    private $deces;


    /**
     * @var DateTime
     *
     * @ORM\Column(name="DatDeces", type="date", nullable=true)
     */
    private $datDeces;


    /**
     * @var string
     *
     * @ORM\Column(name="Memo", type="text", nullable=true)
     */
    private $memo;

//**************************************************Jointures**************************************************************

    /**
     * @ORM\ManyToOne(targetEntity="Medecin", inversedBy="patients")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $medecin;


    //  1 cim10, aplusieurs patients
    /**
     * @ORM\ManyToOne(targetEntity="LibCim10", inversedBy="patients")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $libCim10;


    // un patient est lié a plusieurs inclusions
    /**
     * @ORM\OneToMany(targetEntity="Inclusion", mappedBy="patient", cascade={"all"})
     */
    private $inclusions; // un patient est lié a plusieurs inclusions

//*********************************************GET et SET**************************************************************

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->inclusions = new ArrayCollection();
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get nom
     *
     * @return string
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Set nom
     *
     * @param string $nom
     *
     * @return Patient
     */
    public function setNom($nom)
    {
        $this->nom = strtoupper($nom);

        return $this;
    }

    /**
     * Get nom
     *
     * @return string
     */
    public function getNomNaissance()
    {
        return $this->nomNaissance;
    }

    /**
     * Set nomNaissance
     *
     * @param $nomNaissance
     *
     * @return Patient
     */
    public function setNomNaissance($nomNaissance)
    {
        $this->nomNaissance = strtoupper($nomNaissance);

        return $this;
    }

    /**
     * Get prenom
     *
     * @return string
     */
    public function getPrenom()
    {
        return $this->prenom;
    }

    /**
     * Set prenom
     *
     * @param string $prenom
     *
     * @return Patient
     */
    public function setPrenom($prenom)
    {
        $this->prenom = ucfirst(strtolower($prenom));

        return $this;
    }

    /**
     * Get datNai
     *
     * @return DateTime
     */
    public function getDatNai()
    {
        return $this->datNai;
    }

    /**
     * Set datNai
     *
     * @param DateTime $datNai
     *
     * @return Patient
     */
    public function setDatNai($datNai)
    {
        $this->datNai = $datNai;

        return $this;
    }

    /**
     * Get ipp
     *
     * @return string
     */
    public function getIpp()
    {
        return $this->ipp;
    }

    /**
     * Set ipp
     *
     * @param string $ipp
     *
     * @return Patient
     */
    public function setIpp($ipp)
    {
        $this->ipp = $ipp;

        return $this;
    }

    /**
     * Get memo
     *
     * @return string
     */
    public function getMemo()
    {
        return $this->memo;
    }

    /**
     * Set memo
     *
     * @param string $memo
     *
     * @return Patient
     */
    public function setMemo($memo)
    {
        $this->memo = $memo;

        return $this;
    }

    /**
     * Get datDiag
     *
     * @return DateTime
     */
    public function getDatDiag()
    {
        return $this->datDiag;
    }

    /**
     * Set datDiag
     *
     * @param DateTime $datDiag
     *
     * @return Patient
     */
    public function setDatDiag($datDiag)
    {
        $this->datDiag = $datDiag;

        return $this;
    }

    /**
     * Get txtDiag
     *
     * @return string
     */
    public function getTxtDiag()
    {
        return $this->txtDiag;
    }

    /**
     * Set txtDiag
     *
     * @param string $txtDiag
     *
     * @return Patient
     */
    public function setTxtDiag($txtDiag)
    {
        $this->txtDiag = $txtDiag;

        return $this;
    }

    /**
     * Get datLast
     *
     * @return DateTime
     */
    public function getDatLast()
    {
        return $this->datLast;
    }

    /**
     * Set datLast
     *
     * @param DateTime $datLast
     *
     * @return Patient
     */
    public function setDatLast($datLast)
    {
        $this->datLast = $datLast;

        return $this;
    }

    /**
     * Get sexe
     *
     * @return string
     */
    public function getSexe()
    {
        return $this->sexe;
    }

    /**
     * Set sexe
     *
     * @param string $sexe
     *
     * @return Patient
     */
    public function setSexe($sexe)
    {
        $this->sexe = $sexe;

        return $this;
    }

    /**
     * Get evolution
     *
     * @return string
     */
    public function getEvolution()
    {
        return $this->evolution;
    }

    /**
     * Set evolution
     *
     * @param string $evolution
     *
     * @return Patient
     */
    public function setEvolution($evolution)
    {
        $this->evolution = $evolution;

        return $this;
    }

    /**
     * Get deces
     *
     * @return string
     */
    public function getDeces()
    {
        return $this->deces;
    }

    /**
     * Set deces
     *
     * @param string $deces
     *
     * @return Patient
     */
    public function setDeces($deces)
    {
        $this->deces = $deces;

        return $this;
    }

    /**
     * Get datDeces
     *
     * @return DateTime
     */
    public function getDatDeces()
    {
        return $this->datDeces;
    }

    /**
     * Set datDeces
     *
     * @param DateTime $datDeces
     *
     * @return Patient
     */
    public function setDatDeces($datDeces)
    {
        $this->datDeces = $datDeces;

        return $this;
    }


//***************************Get et Set des Liens**************************************

    /**
     * Get medecin
     *
     * @return Medecin
     */
    public function getMedecin()
    {
        return $this->medecin;
    }

    /**
     * Set medecin
     *
     * @param Medecin $medecin
     *
     * @return Patient
     */
    public function setMedecin(Medecin $medecin = null)
    {
        $this->medecin = $medecin;

        return $this;
    }

    /**
     * Get libCim10
     *
     * @return LibCim10
     */
    public function getLibCim10()
    {
        return $this->libCim10;
    }

    /**
     * Set libCim10
     *
     * @param LibCim10 $libCim10
     *
     * @return Patient
     */
    public function setLibCim10(LibCim10 $libCim10 = null)
    {
        $this->libCim10 = $libCim10;

        return $this;
    }

    public function NomPrenom()
    {
        return $this->nom . " " . $this->prenom;
    }

//*********************************Add/remove/ get inclusion

    /**
     * Add inclusion
     *
     * @param Inclusion $inclusion
     *
     * @return Patient
     */
    public function addInclusion(Inclusion $inclusion)
    {
        $this->inclusions[] = $inclusion;
        $inclusion->setPatient($this);
        return $this;
    }

    /**
     * Remove inclusion
     *
     * @param Inclusion $inclusion
     */
    public function removeInclusion(Inclusion $inclusion)
    {
        $this->inclusions->removeElement($inclusion);
        $inclusion->setPatient(null);
    }

    /**
     * Get inclusions
     *
     * @return Collection
     */
    public function getInclusions()
    {
        return $this->inclusions;
    }

//*********************************Initiales
    public function initial()
    {
        return substr($this->nom, 0, 1) . '-' . substr($this->prenom, 0, 1);
    }

    /**
     * Get cancer
     *
     * @return boolean
     */
    public function getCancer()
    {
        return $this->cancer;
    }

    /**
     * Set cancer
     *
     * @param boolean $cancer
     *
     * @return Patient
     */
    public function setCancer($cancer)
    {
        $this->cancer = $cancer;

        return $this;
    }

    /**
     * Get idInterne
     *
     * @return integer
     */
    public function getIdInterne()
    {
        return $this->idInterne;
    }

    /**
     * Set idInterne
     *
     * @param integer $idInterne
     *
     * @return Patient
     */
    public function setIdInterne($idInterne)
    {
        $this->idInterne = $idInterne;

        return $this;
    }
}

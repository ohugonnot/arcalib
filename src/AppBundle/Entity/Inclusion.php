<?php

namespace AppBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Inclusion
 *
 * @ORM\Table(name="inclusion")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\InclusionRepository")
 */
class Inclusion
{
    const SCREEN = "Screen";
    const OUI_EN_COURS = 'Oui, en cours';
    const OUI_SORTIE = 'Oui, Sortie';
    const NON_FALSE_SCREENING = 'Non, false screening';
    const NON_REFUS = 'Non, refus';
    const AUTRE = 'Autre';

    CONST STATUT = [
        'Screen' => self::SCREEN,
        'Oui, en cours' => self::OUI_EN_COURS,
        'Oui, Sortie' => self::OUI_SORTIE,
        'Non, false screening' => self::NON_FALSE_SCREENING,
        'Non, refus' => self::NON_REFUS,
        'Autre' => self::AUTRE,
    ];

    const SORTIE_STANDARD = "Sortie standard";
    const NON_INCLUS_CRITERE_NON_INC = 'Non inclus, critere Non Inc';
    const NON_INCLUS_REFUS = 'Non inclus, refus';
    const NON_INCLUS_AUTRE = 'Non inclus, autre';
    const PREMATUREE_DCD = 'Prématurée, dcd';
    const PREMATUREE_RETRAIT_CST = 'Prématuré, retrait cst';
    const PREMATUREE_CRITERE_EXCLUSION = 'Prématurée, critère exclusion';
    const PREMATUREE_FALSE_SCREEN = 'Prématurée, false screen';
    const PREMATUREE_CHOIX_PI = 'Prématurée, choix PI';
    const PREMATUREE_PERDU_VUE = 'Prématurée, perdu vue';
    const PREMATUREE_CLOTURE_ESSAI = 'Prématurée, cloture essai';
    const INCONNU = 'Inconnu';

    CONST  MOTIF_SORTIE = [
        'Sortie standard' => self::SORTIE_STANDARD,
        'Non inclus, critere Non Inc' => self::NON_INCLUS_CRITERE_NON_INC,
        'Non inclus, refus' => self::NON_INCLUS_REFUS,
        'Non inclus, autre' => self::NON_INCLUS_AUTRE,
        'Prématurée, dcd' => self::PREMATUREE_DCD,
        'Prématuré, retrait cst' => self::PREMATUREE_RETRAIT_CST,
        'Prématurée, critère exclusion' => self::PREMATUREE_CRITERE_EXCLUSION,
        'Prématurée, false screen' => self::PREMATUREE_FALSE_SCREEN,
        'Prématurée, choix PI' => self::PREMATUREE_CHOIX_PI,
        'Prématurée, perdu vue' => self::PREMATUREE_PERDU_VUE,
        'Prématurée, cloture essai' => self::PREMATUREE_CLOTURE_ESSAI,
        'Autre' => self::AUTRE,
        'Inconnu' => self::INCONNU,
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
     * @var DateTime
     *
     * @ORM\Column(name="DatScr", type="date", nullable=true)
     */
    private $datScr;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="DatCst", type="date", nullable=true)
     */
    private $datCst;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="DatInc", type="date", nullable=true)
     */
    private $datInc;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="DatRan", type="date", nullable=true)
     */
    private $datRan;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="DatJ0", type="date", nullable=true)
     */
    private $datJ0;

    /**
     * @var string
     *
     * @ORM\Column(name="NumInc", type="string", length=50, nullable=true)
     */
    private $numInc;

    /**
     * @var string
     *
     * @ORM\Column(name="BraTrt", type="string", length=50, nullable=true)
     */
    private $braTrt;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="DatOut", type="date", nullable=true)
     */
    private $datOut;


    /**
     * @var \string
     *
     * @ORM\Column(name="Statut", type="string", nullable=true)
     */
    private $statut;

    /**
     * @var \string
     *
     * @ORM\Column(name="MotifSortie", type="string", nullable=true)
     */
    private $motifSortie;

    /**
     * @var string
     *
     * @ORM\Column(name="note", type="text", nullable=true)
     */
    private $note;

    /**
     * @var string
     *
     * @ORM\Column(name="booRa", type="boolean", nullable=true)
     */
    private $booRa;

    /**
     * @var string
     *
     * @ORM\Column(name="booBras", type="boolean", nullable=true)
     */
    private $booBras;

    /**------------------------Variables-LIENS----------------------------- */

    /**
     * @ORM\ManyToOne(targetEntity="Medecin", inversedBy="inclusions")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $medecin;

    /**
     * @ORM\ManyToOne(targetEntity="Arc", inversedBy="inclusions")
     * @ORM\JoinColumn(nullable=true , onDelete="SET NULL")
     */
    private $arc;

    /**
     * @ORM\ManyToOne(targetEntity="Patient", inversedBy="inclusions")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $patient;

    /**
     * @ORM\ManyToOne(targetEntity="Essais", inversedBy="inclusions")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $essai;

    /**
     * @ORM\ManyToOne(targetEntity="Service", inversedBy="inclusions")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $service;

    // L----- relation 1 inclusion -> plusieurs visites-----------------

    /**
     * @ORM\OneToMany(targetEntity="Visite", mappedBy="inclusion", cascade={"all"})
     * @ORM\OrderBy({"date" = "ASC"})
     */
    private $visites;

    /**
     * @ORM\OneToMany(targetEntity="Document", mappedBy="inclusion", cascade={"all"})
     * @ORM\OrderBy({"date" = "ASC"})
     */
    private $documents;

    /**
     * @var ArrayCollection|Traitement[]
     * @ORM\OneToMany(targetEntity="Traitement", mappedBy="inclusion", cascade={"all"})
     */
    private $traitements;

    /**
     * @var ArrayCollection|EI[]
     * @ORM\OneToMany(targetEntity="EI", mappedBy="inclusion", cascade={"all"})
     */
    private $eis;

    /**
     * @var ArrayCollection|Event[]
     * @ORM\OneToMany(targetEntity="Event", mappedBy="inclusion", cascade={"all"})
     */
    private $events;



    /**------------------------constructor----------------------------- */

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->visites = new ArrayCollection();
        $this->documents = new ArrayCollection();
        $this->traitements = new ArrayCollection();
        $this->eis = new ArrayCollection();
        $this->events = new ArrayCollection();
    }

    /**------------------------Get et set variables----------------------------- */
    /**
     * Get id
     *
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return Event[]|ArrayCollection
     */
    public function getEvents()
    {
        return $this->events;
    }

    /**
     * @param Event $event
     * @return $this
     */
    public function addEvent(Event $event)
    {
        if (!$this->events->contains($event)) {
            $this->events[] = $event;
            $event->setInclusion($this);
        }
        return $this;
    }

    /**
     * @param Event $event
     * @return $this
     */
    public function removeEvent(Event $event)
    {
        if ($this->events->contains($event)) {
            $this->events->removeElement($event);
            $event->setInclusion(null);
        }
        return $this;
    }


    /**
     * Get datScr
     *
     * @return DateTime
     */
    public function getDatScr()
    {
        return $this->datScr;
    }

    /**
     * Set datScr
     *
     * @param DateTime $datScr
     *
     * @return inclusion
     */
    public function setDatScr($datScr)
    {
        $this->datScr = $datScr;

        return $this;
    }

    /**
     * Get datCst
     *
     * @return DateTime
     */
    public function getDatCst()
    {
        return $this->datCst;
    }

    /**
     * Set datCst
     *
     * @param DateTime $datCst
     *
     * @return inclusion
     */
    public function setDatCst($datCst)
    {
        $this->datCst = $datCst;

        return $this;
    }

    /**
     * Get datInc
     *
     * @return DateTime
     */
    public function getDatInc()
    {
        return $this->datInc;
    }

    /**
     * Set datInc
     *
     * @param DateTime $datInc
     *
     * @return inclusion
     */
    public function setDatInc($datInc)
    {
        $this->datInc = $datInc;

        return $this;
    }

    /**
     * Get datRan
     *
     * @return DateTime
     */
    public function getDatRan()
    {
        return $this->datRan;
    }

    /**
     * Set datRan
     *
     * @param DateTime $datRan
     *
     * @return inclusion
     */
    public function setDatRan($datRan)
    {
        $this->datRan = $datRan;

        return $this;
    }

    /**
     * Get datJ0
     *
     * @return DateTime
     */
    public function getDatJ0()
    {
        return $this->datJ0;
    }

    /**
     * Set datJ0
     *
     * @param DateTime $datJ0
     *
     * @return inclusion
     */
    public function setDatJ0($datJ0)
    {
        $this->datJ0 = $datJ0;

        return $this;
    }

    /**
     * Get numInc
     *
     * @return string
     */
    public function getNumInc()
    {
        return $this->numInc;
    }

    /**
     * Set numInc
     *
     * @param string $numInc
     *
     * @return inclusion
     */
    public function setNumInc($numInc)
    {
        $this->numInc = $numInc;

        return $this;
    }

    /**
     * Get braTrt
     *
     * @return string
     */
    public function getBraTrt()
    {
        return $this->braTrt;
    }

    /**
     * Set braTrt
     *
     * @param string $braTrt
     *
     * @return inclusion
     */
    public function setBraTrt($braTrt)
    {
        $this->braTrt = $braTrt;

        return $this;
    }

    /**
     * Get datOut
     *
     * @return DateTime
     */
    public function getDatOut()
    {
        return $this->datOut;
    }

    /**
     * Set datOut
     *
     * @param DateTime $datOut
     *
     * @return inclusion
     */
    public function setDatOut($datOut)
    {
        $this->datOut = $datOut;

        return $this;
    }

    /**
     * Get statut
     *
     * @return string
     */
    public function getStatut()
    {
        return $this->statut;
    }

    /**
     * Set statut
     *
     * @param string $statut
     *
     * @return Inclusion
     */
    public function setStatut($statut)
    {
        $this->statut = $statut;

        return $this;
    }

    /**
     * Get motifSortie
     *
     * @return string
     */
    public function getMotifSortie()
    {
        return $this->motifSortie;
    }

    /**
     * Set motifSortie
     *
     * @param string $motifSortie
     *
     * @return Inclusion
     */
    public function setMotifSortie($motifSortie)
    {
        $this->motifSortie = $motifSortie;

        return $this;
    }

    /**
     * Get note
     *
     * @return string
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * Set note
     *
     * @param string $note
     *
     * @return Inclusion
     */
    public function setNote($note)
    {
        $this->note = $note;

        return $this;
    }


    /**------------------------get/ Set des liens----------------------------- */

    /**
     * Get medecin
     *
     * @return medecin
     */
    public function getMedecin()
    {
        return $this->medecin;
    }

    /**
     * Set medecin
     *
     * @param medecin $medecin
     *
     * @return Inclusion
     */
    public function setMedecin(medecin $medecin = null)
    {
        $this->medecin = $medecin;

        return $this;
    }

    /**
     * Get arc
     *
     * @return Arc
     */
    public function getArc()
    {
        return $this->arc;
    }

    /**
     * Set arc
     *
     * @param Arc $arc
     *
     * @return Inclusion
     */
    public function setArc(Arc $arc = null)
    {
        $this->arc = $arc;
        return $this;
    }

    /**
     * Get patient
     *
     * @return Patient
     */
    public function getPatient()
    {
        return $this->patient;
    }

    /**
     * Set patient
     *
     * @param Patient $patient
     *
     * @return Inclusion
     */
    public function setPatient(Patient $patient = null)
    {
        $this->patient = $patient;

        return $this;
    }

    /**
     * Get essai
     *
     * @return Essais
     */
    public function getEssai()
    {
        return $this->essai;
    }

    /**
     * Set essai
     *
     * @param Essais $essai
     *
     * @return Inclusion
     */
    public function setEssai(Essais $essai = null)
    {
        $this->essai = $essai;

        return $this;
    }

    /**------------------------get/set visite 1 Inc-> n vis,    ---------------------------- */

    /**
     * Add visite
     *
     * @param Visite $visite
     *
     * @return Inclusion
     */
    public function addVisite(Visite $visite)
    {

        $this->visites[] = $visite;
        $visite->setInclusion($this);
        return $this;
    }

    /**
     * Remove visite
     *
     * @param Visite $visite
     */
    public function removeVisite(Visite $visite)
    {
        $this->visites->removeElement($visite);
        $visite->setInclusion(null);
    }

    /**
     * Get visites
     *
     * @return Collection
     */
    public function getVisites()
    {
        return $this->visites;
    }

    /**
     * Get service
     *
     * @return Service
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * Set service
     *
     * @param Service $service
     *
     * @return Inclusion
     */
    public function setService(Service $service = null)
    {
        $this->service = $service;

        return $this;
    }

    /**
     * Get booRa
     *
     * @return boolean
     */
    public function getBooRa()
    {
        return $this->booRa;
    }

    /**
     * Set booRa
     *
     * @param boolean $booRa
     *
     * @return Inclusion
     */
    public function setBooRa($booRa)
    {
        $this->booRa = $booRa;

        return $this;
    }

    /**
     * Get booBras
     *
     * @return boolean
     */
    public function getBooBras()
    {
        return $this->booBras;
    }

    /**
     * Set booBras
     *
     * @param boolean $booBras
     *
     * @return Inclusion
     */
    public function setBooBras($booBras)
    {
        $this->booBras = $booBras;

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
     * @return Inclusion
     */
    public function setIdInterne($idInterne)
    {
        $this->idInterne = $idInterne;

        return $this;
    }

    /**
     * Add document
     *
     * @param Document $document
     *
     * @return Inclusion
     */
    public function addDocument(Document $document)
    {
        $this->documents[] = $document;
        $document->setInclusion($this);

        return $this;
    }

    /**
     * Remove document
     *
     * @param Document $document
     */
    public function removeDocument(Document $document)
    {
        $this->documents->removeElement($document);
    }

    /**
     * Get documents
     *
     * @return Collection
     */
    public function getDocuments()
    {
        return $this->documents;
    }

    /**
     * @param Traitement $traitement
     * @return $this
     */
    public function addTraitement(Traitement $traitement)
    {
        if(!$this->traitements->contains($traitement)) {
            $this->traitements[] = $traitement;
        }
        return $this;
    }

    /**
     * @param Traitement $traitement
     * @return $this
     */
    public function removeTraitement(Traitement $traitement)
    {
        if($this->traitements->contains($traitement)) {
            $this->traitements->removeElement($traitement);
        }
        return $this;
    }

    /**
     * @return Traitement[]|ArrayCollection
     */
    public function getTraitements()
    {
        return $this->traitements;
    }

    /**
     * @return EI[]|ArrayCollection
     */
    public function getEis()
    {
        return $this->eis;
    }

    /**
     * @param EI $ei
     * @return $this
     */
    public function addEi(EI $ei)
    {
        if (!$this->eis->contains($ei)) {
            $this->eis[] = $ei;
            $ei->setInclusion($this);
        }
        return $this;
    }

    /**
     * @param EI $ei
     * @return $this
     */
    public function removeEi(EI $ei)
    {
        if ($this->eis->contains($ei)) {
            $this->eis->removeElement($ei);
            $ei->setInclusion(null);
        }
        return $this;
    }
}

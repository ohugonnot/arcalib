<?php

namespace AppBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * Essais
 *
 * @ORM\Table(name="essais")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\EssaisRepository")
 * @UniqueEntity("numCt", message="N° Clinical Trial {{ value }} est déjà utilisée.")
 * @UniqueEntity("nom", message="Nom {{ value }} est déjà utilisée.")
 * @UniqueEntity("numEudract", message="N° Eudra CT {{ value }} est déjà utilisée.")
 */
class Essais
{

    // Ce qui sera sauver en base
    const FAISABILITE_EN_ATTENTE = "Faisabilité en attente";
    const CONVENTION_SIGNATURE = "Convention signature";
    const ATTENTE_DE_MEP = "Attente de MEP";
    const INCLUSIONS_OUVERTES = "Inclusions ouvertes";
    const INCLUSIONS_GELEES = "Inclusions gelées";
    const INCLUSIONS_CLOSES_SUIVI = "Inclusions closes, suivi";
    const QUERIES_ET_FINALISATION = "Queries et finalisation";
    const CLOSE_EN_ATTENTE_PAYEMENT = "Clos, en attente payement";
    const ARCHIVE = "Archivé";
    const AUTRE = "Autre";
    const REFUS = "Refus";

    // Ce qu'on pourra lire dans les selecteurs sur l'outil
    CONST STATUT = [
        'Faisabilité en attente' => self::FAISABILITE_EN_ATTENTE,
        'Convention signature' => self::CONVENTION_SIGNATURE,
        'Attente de MEP' => self::ATTENTE_DE_MEP,
        'Inclusions ouvertes' => self::INCLUSIONS_OUVERTES,
        'Inclusions gelées' => self::INCLUSIONS_GELEES,
        'Inclusions closes, suivi' => self::INCLUSIONS_CLOSES_SUIVI,
        'Queries et finalisation' => self::QUERIES_ET_FINALISATION,
        'Clos, en attente payement' => self::CLOSE_EN_ATTENTE_PAYEMENT,
        'Archivé' => self::ARCHIVE,
        'Autre' => self::AUTRE,
        'Refus' => self::REFUS,
    ];

    const OBSERVATIONELLE = "Observationelle";
    const INTERV_TYPE_1 = "Interv-type 1";
    const INTERV_TYPE_2 = "Interv-type 2";
    const TYPE_3_NI = "type 3-NI";
    const REGISTRE = "Registre";
    const COLLECTION_BIOLOGIQUE = "Collection biologique";
    const NA = "NA";

    CONST TYPE = [
        'Observationelle' => self::OBSERVATIONELLE,
        'Interv-type 1' => self::INTERV_TYPE_1,
        'Interv-type 2' => self::INTERV_TYPE_2,
        'type 3-NI' => self::TYPE_3_NI,
        'Registre' => self::REGISTRE,
        'Collection biologique' => self::COLLECTION_BIOLOGIQUE,
        'Autre' => self::AUTRE,
        'NA' => self::NA,
    ];

    const PHASE_I = "Phase I";
    const PHASE_II = "Phase II";
    const PHASE_II_III = "Phase II-III";
    const PHASE_III = "Phase III";
    const PHASE_IV = "Phase IV";

    CONST PHASE = [
        'Phase I' => self::PHASE_I,
        'Phase II' => self::PHASE_II,
        'Phase II-III' => self::PHASE_II_III,
        'Phase III' => self::PHASE_III,
        'Phase IV' => self::PHASE_IV,
        'Autre' => self::AUTRE,
        'NA' => self::NA,
    ];


    const HOSPITALIER = "Hospitalier";
    const SOCIETE_SAVANTE = "Société savante";
    const INDUSTRIEL = "Industriel";

    CONST PROM = [
        'Hospitalier' => self::HOSPITALIER,
        'Société savante' => self::SOCIETE_SAVANTE,
        'Industriel' => self::INDUSTRIEL,
        'Autre' => self::AUTRE,
        'NA' => self::NA,
    ];

    const INTERNE = "Interne";
    const EXTERNE = "Externe";

    CONST AUTO_PROM = [
        'Interne' => self::INTERNE,
        'Externe' => self::EXTERNE,
        'Autre' => self::AUTRE,
    ];

    const OUI = "Oui";
    const NON = "Non";
    const PARTIEL = "Partiel";

    CONST URCGES = [
        'Oui' => self::OUI,
        'Non' => self::NON,
        'Partiel' => self::PARTIEL,
        'NA' => self::NA,
    ];


    const DIRECT = "Direct";
    const DOUBLE_AVEC_SURCOUT = "Double avec Surcout";
    const TRIPARTITE_AVEC_IP_OBSERVATEUR = "Tripartite avec IP observateur";
    const CONVENTION_UNIQUE = "Convention unique";
    const PAS_DE_CONVENTION = "Pas de convention";

    CONST TYPE_CONV = [
        'Direct' => self::DIRECT,
        'Double avec Surcout' => self::DOUBLE_AVEC_SURCOUT,
        'Tripartite avec IP observateur' => self::TRIPARTITE_AVEC_IP_OBSERVATEUR,
        'Convention unique' => self::CONVENTION_UNIQUE,
        'Pas de convention' => self::PAS_DE_CONVENTION,
        'NA' => self::NA,
    ];

    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({"protocole"})
     */
    private $id;


    /**
     * @var string
     *
     * @ORM\Column(name="Nom", type="string", length=100, unique=true)
     * @Serializer\Groups({"protocole"})
     */
    private $nom;


    /**
     * @var string
     *
     * @ORM\Column(name="Statut", type="string", length=100, nullable=true)
     * @Serializer\Groups({"protocole"})
     */
    private $statut;


    /**
     * @var string
     *
     * @ORM\Column(name="Titre", type="text", nullable=true)
     * @Serializer\Groups({"protocole"})
     */
    private $titre;


    /**
     * @var string
     *
     * @ORM\Column(name="NumeroInterne", type="string", length=20, nullable=true)
     * @Serializer\Groups({"protocole"})
     */
    private $numeroInterne;


    /**
     * @var string
     *
     * @ORM\Column(name="NumeroCentre", type="string", length=20, nullable=true)
     * @Serializer\Groups({"protocole"})
     */
    private $numeroCentre;


    /**
     * @var string
     *
     * @ORM\Column(name="TypeEssai", type="string", length=100, nullable=true)
     * @Serializer\Groups({"protocole"})
     */
    private $typeEssai;


    /**
     * @var string
     *
     * @ORM\Column(name="StadeEss", type="string", length=30, nullable=true)
     * @Serializer\Groups({"protocole"})
     */
    private $stadeEss;


    /**
     * @var DateTime
     *
     * @ORM\Column(name="DateOuv", type="date", nullable=true)
     * @Serializer\Groups({"protocole"})
     */
    private $dateOuv;


    /**
     * @var DateTime
     *
     * @ORM\Column(name="DateFinInc", type="date", nullable=true)
     * @Serializer\Groups({"protocole"})
     */
    private $dateFinInc;


    /**
     * @var DateTime
     *
     * @ORM\Column(name="DateClose", type="date", nullable=true)
     * @Serializer\Groups({"protocole"})
     */
    private $dateClose;


    /**
     * @var string
     *
     * @ORM\Column(name="TypeProm", type="string", length=50, nullable=true)
     * @Serializer\Groups({"protocole"})
     */
    private $typeProm;


    /**
     * @var string
     *
     * @ORM\Column(name="Autoprom", type="string", length=20, nullable=true)
     * @Serializer\Groups({"protocole"})
     */
    private $autoProm;


    /**
     * @var string
     *
     * @ORM\Column(name="Prom", type="string", length=100, nullable=true)
     * @Serializer\Groups({"protocole"})
     */
    private $prom;


// -----------coordonnées du contact---------------------

    /**
     * @var string
     *
     * @ORM\Column(name="ContactNom", type="string", length=255, nullable=true)
     * @Serializer\Groups({"protocole"})
     */
    private $contactNom;


    /**
     * @var string
     * @Assert\Email(
     *     message = "L'email '{{ value }}' n'est pas un email valide.",
     * )
     * @ORM\Column(name="ContactMail", type="string", length=255, nullable=true)
     * @Serializer\Groups({"protocole"})
     */
    private $contactMail;


    /**
     * @var string
     *
     * @ORM\Column(name="ContactTel", type="string", length=50, nullable=true)
     * @Serializer\Groups({"protocole"})
     */
    private $contactTel;


// -----------Divers----------------------------------------


    /**
     * @var string
     *
     * @ORM\Column(name="EcrfLink", type="string", length=255, nullable=true)
     * @Serializer\Groups({"protocole"})
     */
    private $ecrfLink;


    /**
     * @var string
     *
     * @ORM\Column(name="Notes", type="text", nullable=true)
     * @Serializer\Groups({"protocole"})
     */
    private $notes;


//------------------------Gestion/ finances---------
    /**
     * @var string
     *
     * @ORM\Column(name="UrcGes", type="string", length=10, nullable=true)
     * @Serializer\Groups({"protocole"})
     */
    private $urcGes;


    /**
     * @var bool
     *
     * @ORM\Column(name="Sigrec", type="boolean", nullable=true)
     * @Serializer\Groups({"protocole"})
     */
    private $sigrec;


    /**
     * @var bool
     *
     * @ORM\Column(name="Sigaps", type="boolean", nullable=true)
     * @Serializer\Groups({"protocole"})
     */
    private $sigaps;


    /**
     * @var bool
     *
     * @ORM\Column(name="Emrc", type="boolean", nullable=true)
     * @Serializer\Groups({"protocole"})
     */
    private $emrc;


    /**
     * @var bool
     *
     * @ORM\Column(name="eudraCtNd", type="boolean", nullable=true)
     * @Serializer\Groups({"protocole"})
     */
    private $eudraCtNd;

    /**
     * @var bool
     *
     * @ORM\Column(name="CtNd", type="boolean", nullable=true)
     * @Serializer\Groups({"protocole"})
     */
    private $ctNd;

    /**
     * @var bool
     *
     * @ORM\Column(name="Cancer", type="boolean", nullable=true)
     * @Serializer\Groups({"protocole"})
     */
    private $cancer;


    /**
     * @var string
     *
     * @ORM\Column(name="TypeConv", type="string", length=100, nullable=true)
     * @Serializer\Groups({"protocole"})
     */
    private $typeConv;

    /**
     * @var DateTime
     *
     * @ORM\Column(name="DateSignConv", type="date", nullable=true)
     * @Serializer\Groups({"protocole"})
     */
    private $dateSignConv;

    /**
     * @var string
     *
     * @ORM\Column(name="NumEudract", type="string", length=30, nullable=true, unique=true)
     * @Serializer\Groups({"protocole"})
     */
    private $numEudract;

    /**
     * @var string
     *
     * @ORM\Column(name="NumCt", type="string", length=30, nullable=true, unique=true)
     * @Serializer\Groups({"protocole"})
     */
    private $numCt;

    /**
     * @var string
     *
     * @ORM\Column(name="IntLink", type="string", length=150, nullable=true)
     * @Serializer\Groups({"protocole"})
     */
    private $intLink;


    /**
     * @var integer
     * @Assert\GreaterThan(0)
     * @ORM\Column(name="Objectif", type="integer", nullable=true)
     * @Serializer\Groups({"protocole"})
     */
    private $objectif;


//****************************variables de Liaisons********************************************

// Ici la relation  n Essais, n Tags
    /**
     * Many Groups have Many Users.
     * @ORM\ManyToMany(targetEntity="Tag", mappedBy="essais", fetch="EXTRA_LAZY")
     * @ORM\OrderBy({"nom" = "ASC"})
     * @Serializer\Groups({"protocole"})
     */
    private $tags;

// Ici la relation  1 Essai, 1 ARC mais 1 ARc  N Essais
    /**
     * @ORM\ManyToOne(targetEntity="Arc", inversedBy="essais" , cascade={"persist"})
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     * @Serializer\Exclude
     */
    private $arc;


// Ici la relation  1 Essai, 1 Medecin mais 1 Medecin  N Essais
    /**
     * @ORM\ManyToOne(targetEntity="Medecin", inversedBy="essais" , cascade={"persist"})
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     * @Serializer\Exclude
     */
    private $medecin;


// Ici la relation  1 Essai, many Inclusions mais 1 Inclusion est lié a 1 essai
    /**
     * @ORM\OneToMany(targetEntity="Inclusion", mappedBy="essai", cascade={"all"})
     * @ORM\OrderBy({"numInc" = "ASC"})
     * @Serializer\Exclude
     */
    private $inclusions;


// Ici la relation 1 essai, many factures
    /**
     * @ORM\OneToMany(targetEntity="Facture", mappedBy="essai", cascade={"all"})
     * @Serializer\Exclude
     */
    private $factures;

    /**
     * @ORM\OneToMany(targetEntity="Annuaire", mappedBy="essai", cascade={"all"})
     * @Serializer\Exclude
     */
    private $annuaires;

    /**
     * @ORM\OneToOne(targetEntity="EssaiDetail", inversedBy="essai", cascade={"all"})
     * @ORM\JoinColumn(name="detail_id", referencedColumnName="id", onDelete="CASCADE")
     */
    private $detail;


    /**
     * @ORM\ManyToMany(targetEntity="User", mappedBy="essais" , cascade={"persist"}, fetch="EXTRA_LAZY"))
     */
    private $users;

    /**
     * @ORM\ManyToMany(targetEntity="Service", inversedBy="essais" , cascade={"persist"}, fetch="EXTRA_LAZY"))
     * @ORM\JoinTable(name="services_essais")
     * @ORM\OrderBy({"nom" = "ASC"})
     */
    private $services;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Assert\File(mimeTypes={ "application/pdf" })
     */
    private $synopsis;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Assert\File(mimeTypes={ "application/pdf" })
     */
    private $protocole;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Assert\File(mimeTypes={ "application/pdf" })
     */
    private $crf;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Assert\File(mimeTypes={ "application/pdf" })
     */
    private $nip;

    /**
     * @ORM\Column(type="string", nullable=true)
     * @Assert\File(mimeTypes={ "application/pdf" })
     */
    private $procedurePDF;


    /**
     * @ORM\OneToMany(targetEntity="DocumentEssai", mappedBy="essai", cascade={"all"})
     * @ORM\OrderBy({"date" = "ASC"})
     */
    private $documents;

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->inclusions = new ArrayCollection();
        $this->factures = new ArrayCollection();
        $this->tags = new ArrayCollection();
        $this->annuaires = new ArrayCollection();
        $this->users = new ArrayCollection();
        $this->services = new ArrayCollection();
        $this->documents = new ArrayCollection();
    }

    public function getSynopsis()
    {
        return $this->synopsis;
    }

    public function setSynopsis($synopsis)
    {
        $this->synopsis = $synopsis;

        return $this;
    }

    public function getProtocole()
    {
        return $this->protocole;
    }

    public function setProtocole($protocole)
    {
        $this->protocole = $protocole;

        return $this;
    }

    public function getCrf()
    {
        return $this->crf;
    }

    public function setCrf($crf)
    {
        $this->crf = $crf;

        return $this;
    }

    public function getNip()
    {
        return $this->nip;
    }

    public function setNip($nip)
    {
        $this->nip = $nip;

        return $this;
    }

    public function getProcedure()
    {
        return $this->procedurePDF;
    }

    public function setProcedure($procedurePDF)
    {
        $this->procedurePDF = $procedurePDF;

        return $this;
    }

    public function getProcedurePDF()
    {
        return $this->procedurePDF;
    }

//****************************CONSTRUCTOR********************************************

    public function setProcedurePDF($procedurePDF)
    {
        $this->procedurePDF = $procedurePDF;

        return $this;
    }


//****************************GET ET SET********************************************

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
     * @return Essais
     */

    public function setNom($nom)
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get titre
     *
     * @return string
     */
    public function getTitre()
    {
        return $this->titre;
    }

    /**
     * Set titre
     *
     * @param string $titre
     *
     * @return Essais
     */
    public function setTitre($titre)
    {
        $this->titre = $titre;

        return $this;
    }

    /**
     * Get numeroInterne
     *
     * @return string
     */
    public function getNumeroInterne()
    {
        return $this->numeroInterne;
    }

    /**
     * Set numeroInterne
     *
     * @param string $numeroInterne
     *
     * @return Essais
     */
    public function setNumeroInterne($numeroInterne)
    {
        $this->numeroInterne = $numeroInterne;

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
     * @return Essais
     */
    public function setStatut($statut)
    {
        $this->statut = $statut;

        return $this;
    }

    /**
     * Get numeroCentre
     *
     * @return string
     */
    public function getNumeroCentre()
    {
        return $this->numeroCentre;
    }

    /**
     * Set numeroCentre
     *
     * @param string $numeroCentre
     *
     * @return Essais
     */
    public function setNumeroCentre($numeroCentre)
    {
        $this->numeroCentre = $numeroCentre;

        return $this;
    }



// -----------------Get et set de dates---------------------------------------------------

    /**
     * Get dateOuv
     *
     * @return DateTime
     */
    public function getDateOuv()
    {
        return $this->dateOuv;
    }

    /**
     * Set dateOuv
     *
     * @param DateTime $dateOuv
     *
     * @return Essais
     */
    public function setDateOuv($dateOuv)
    {
        $this->dateOuv = $dateOuv;

        return $this;
    }

    /**
     * Get dateFinInc
     *
     * @return DateTime
     */
    public function getDateFinInc()
    {
        return $this->dateFinInc;
    }

    /**
     * Set dateFinInc
     *
     * @param DateTime $dateFinInc
     *
     * @return Essais
     */
    public function setDateFinInc($dateFinInc)
    {
        $this->dateFinInc = $dateFinInc;

        return $this;
    }

    /**
     * Get dateClose
     *
     * @return DateTime
     */
    public function getDateClose()
    {
        return $this->dateClose;
    }

    /**
     * Set dateClose
     *
     * @param DateTime $dateClose
     *
     * @return Essais
     */
    public function setDateClose($dateClose)
    {
        $this->dateClose = $dateClose;

        return $this;
    }

    /**
     * Get typeEssai
     *
     * @return string
     */
    public function getTypeEssai()
    {
        return $this->typeEssai;
    }

    /**
     * Set typeEssai
     *
     * @param string $typeEssai
     *
     * @return Essais
     */
    public function setTypeEssai($typeEssai)
    {
        $this->typeEssai = $typeEssai;

        return $this;
    }

    /**
     * Get typeProm
     *
     * @return string
     */
    public function getTypeProm()
    {
        return $this->typeProm;
    }

    /**
     * Set typeProm
     *
     * @param string $typeProm
     *
     * @return Essais
     */
    public function setTypeProm($typeProm)
    {
        $this->typeProm = $typeProm;

        return $this;
    }

    /**
     * Get autoProm
     *
     * @return string
     */
    public function getAutoProm()
    {
        return $this->autoProm;
    }

    /**
     * Set autoProm
     *
     * @param string $autoProm
     *
     * @return Essais
     */
    public function setAutoProm($autoProm)
    {
        $this->autoProm = $autoProm;

        return $this;
    }

    /**
     * Get prom
     *
     * @return string
     */
    public function getProm()
    {
        return $this->prom;
    }

    /**
     * Set prom
     *
     * @param string $prom
     *
     * @return Essais
     */
    public function setProm($prom)
    {
        $this->prom = $prom;

        return $this;
    }

    /**
     * Get stadeEss
     *
     * @return string
     */
    public function getStadeEss()
    {
        return $this->stadeEss;
    }

    /**
     * Set stadeEss
     *
     * @param string $stadeEss
     *
     * @return Essais
     */
    public function setStadeEss($stadeEss)
    {
        $this->stadeEss = $stadeEss;

        return $this;
    }

    /**
     * Get contactNom
     *
     * @return string
     */
    public function getContactNom()
    {
        return $this->contactNom;
    }

    /**
     * Set contactNom
     *
     * @param string $contactNom
     *
     * @return Essais
     */
    public function setContactNom($contactNom)
    {
        $this->contactNom = $contactNom;

        return $this;
    }

    /**
     * Get contactMail
     *
     * @return string
     */
    public function getContactMail()
    {
        return $this->contactMail;
    }

    /**
     * Set contactMail
     *
     * @param string $contactMail
     *
     * @return Essais
     */
    public function setContactMail($contactMail)
    {
        $this->contactMail = $contactMail;

        return $this;
    }

    /**
     * Get contactTel
     *
     * @return string
     */
    public function getContactTel()
    {
        return $this->contactTel;
    }

    /**
     * Set contactTel
     *
     * @param string $contactTel
     *
     * @return Essais
     */
    public function setContactTel($contactTel)
    {
        $this->contactTel = $contactTel;

        return $this;
    }

    /**
     * Get ecrfLink
     *
     * @return string
     */
    public function getEcrfLink()
    {
        return $this->ecrfLink;
    }

    /**
     * Set ecrfLink
     *
     * @param string $ecrfLink
     *
     * @return Essais
     */
    public function setEcrfLink($ecrfLink)
    {
        $this->ecrfLink = $ecrfLink;

        return $this;
    }

    /**
     * Get notes
     *
     * @return string
     */
    public function getNotes()
    {
        return $this->notes;
    }

    /**
     * Set notes
     *
     * @param string $notes
     *
     * @return Essais
     */
    public function setNotes($notes)
    {
        $this->notes = $notes;

        return $this;
    }

    /**
     * Get urcGes
     *
     * @return string
     */
    public function getUrcGes()
    {
        return $this->urcGes;
    }

    /**
     * Set urcGes
     *
     * @param string $urcGes
     *
     * @return Essais
     */
    public function setUrcGes($urcGes)
    {
        $this->urcGes = $urcGes;

        return $this;
    }

    /**
     * Get sigrec
     *
     * @return bool
     */
    public function getSigrec()
    {
        return $this->sigrec;
    }

    /**
     * Set sigrec
     *
     * @param boolean $sigrec
     *
     * @return Essais
     */
    public function setSigrec($sigrec)
    {
        $this->sigrec = $sigrec;

        return $this;
    }

    /**
     * Get sigaps
     *
     * @return bool
     */
    public function getSigaps()
    {
        return $this->sigaps;
    }

    /**
     * Set sigaps
     *
     * @param boolean $sigaps
     *
     * @return Essais
     */
    public function setSigaps($sigaps)
    {
        $this->sigaps = $sigaps;

        return $this;
    }

    /**
     * Get emrc
     *
     * @return bool
     */
    public function getEmrc()
    {
        return $this->emrc;
    }

    /**
     * Set emrc
     *
     * @param boolean $emrc
     *
     * @return Essais
     */
    public function setEmrc($emrc)
    {
        $this->emrc = $emrc;

        return $this;
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
     * @return Essais
     */
    public function setCancer($cancer)
    {
        $this->cancer = $cancer;

        return $this;
    }
    //-----------------------------------Gestion-------------------------------------

    /**
     * Get typeConv
     *
     * @return string
     */
    public function getTypeConv()
    {
        return $this->typeConv;
    }

    /**
     * Set typeConv
     *
     * @param string $typeConv
     *
     * @return Essais
     */
    public function setTypeConv($typeConv)
    {
        $this->typeConv = $typeConv;

        return $this;
    }

    /**
     * Get dateSignConv
     *
     * @return DateTime
     */
    public function getDateSignConv()
    {
        return $this->dateSignConv;
    }

    /**
     * Set dateSignConv
     *
     * @param DateTime $dateSignConv
     *
     * @return Essais
     */
    public function setDateSignConv($dateSignConv)
    {
        $this->dateSignConv = $dateSignConv;

        return $this;
    }

    /**
     * Get numEudract
     *
     * @return string
     */
    public function getNumEudract()
    {
        return $this->numEudract;
    }

    /**
     * Set numEudract
     *
     * @param string $numEudract
     *
     * @return Essais
     */
    public function setNumEudract($numEudract)
    {
        $this->numEudract = $numEudract;

        return $this;
    }

    /**
     * Get numCt
     *
     * @return string
     */
    public function getNumCt()
    {
        return $this->numCt;
    }

    /**
     * Set numCt
     *
     * @param string $numCt
     *
     * @return Essais
     */
    public function setNumCt($numCt)
    {
        $this->numCt = $numCt;

        return $this;
    }

    /**
     * Get intLink
     *
     * @return string
     */
    public function getIntLink()
    {
        return $this->intLink;
    }

    /**
     * Set intLink
     *
     * @param string $intLink
     *
     * @return Essais
     */
    public function setIntLink($intLink)
    {
        $this->intLink = $intLink;

        return $this;
    }

    /**
     * Get objectif
     *
     * @return integer
     */
    public function getObjectif()
    {
        return $this->objectif;
    }

    /**
     * Set objectif
     *
     * @param integer $objectif
     *
     * @return Essais
     */
    public function setObjectif($objectif)
    {
        $this->objectif = $objectif;

        return $this;
    }


    //-----------------------------------------GET et SET  de Liens--------------------------------------------------

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
     * @return Essais
     */
    public function setArc(Arc $arc = null)
    {
        $this->arc = $arc;

        return $this;
    }

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
     * @return Essais
     */
    public function setMedecin(Medecin $medecin = null)
    {
        $this->medecin = $medecin;

        return $this;
    }

    /**
     * Add inclusion
     *
     * @param Inclusion $inclusion
     *
     * @return Essais
     */
    public function addInclusion(Inclusion $inclusion)
    {
        $this->inclusions[] = $inclusion;
        $inclusion->setEssai($this);
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
        $inclusion->setEssai(null);
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


    /**
     * Add facture
     *
     * @param Facture $facture
     *
     * @return Essais
     */
    public function addFacture(Facture $facture)
    {
        $this->factures[] = $facture;
        $facture->setEssai($this);
        return $this;
    }

    /**
     * Remove facture
     *
     * @param Facture $facture
     */
    public function removeFacture(Facture $facture)
    {
        $this->factures->removeElement($facture);
        $facture->setEssai(null);
    }

    /**
     * Get factures
     *
     * @return Collection
     */
    public function getFactures()
    {
        return $this->factures;
    }

    /**
     * Add tag
     *
     * @param Tag $tag
     *
     * @return Essais
     */
    public function addTag(Tag $tag)
    {
        $this->tags[] = $tag;
        $tag->addEssai($this);

        return $this;
    }

    public function clearTags()
    {
        foreach ($this->tags as $tag) {
            $this->removeTag($tag);
        }
    }

    /**
     * Remove tag
     *
     * @param Tag $tag
     */
    public function removeTag(Tag $tag)
    {
        $this->tags->removeElement($tag);
        $tag->removeEssai($this);
    }

    /**
     * Get tags
     *
     * @return Collection
     */
    public function getTags()
    {
        return $this->tags;
    }

    /**
     * Get detail
     *
     * @return EssaiDetail
     */
    public function getDetail()
    {
        return $this->detail;
    }

    /**
     * Set detail
     *
     * @param EssaiDetail $detail
     *
     * @return Essais
     */
    public function setDetail(EssaiDetail $detail = null)
    {
        $this->detail = $detail;

        return $this;
    }

    /**
     * Add annuaire
     *
     * @param Annuaire $annuaire
     *
     * @return Essais
     */
    public function addAnnuaire(Annuaire $annuaire)
    {
        if (!$this->annuaires->contains($annuaire)) {
            $this->annuaires[] = $annuaire;
            $annuaire->setEssai($this);
        }
        return $this;
    }

    /**
     * Remove annuaire
     *
     * @param Annuaire $annuaire
     */
    public function removeAnnuaire(Annuaire $annuaire)
    {
        if ($this->annuaires->contains($annuaire)) {
            $annuaire->setEssai(null);
            $this->annuaires->removeElement($annuaire);
        }
    }

    /**
     * Get annuaires
     *
     * @return Collection
     */
    public function getAnnuaires()
    {
        return $this->annuaires;
    }

    /**
     * Get eudraCtNd
     *
     * @return boolean
     */
    public function getEudraCtNd()
    {
        return $this->eudraCtNd;
    }

    /**
     * Set eudraCtNd
     *
     * @param boolean $eudraCtNd
     *
     * @return Essais
     */
    public function setEudraCtNd($eudraCtNd)
    {
        $this->eudraCtNd = $eudraCtNd;

        return $this;
    }

    /**
     * Get ctNd
     *
     * @return boolean
     */
    public function getCtNd()
    {
        return $this->ctNd;
    }

    /**
     * Set ctNd
     *
     * @param boolean $ctNd
     *
     * @return Essais
     */
    public function setCtNd($ctNd)
    {
        $this->ctNd = $ctNd;

        return $this;
    }

    /**
     * Add user
     *
     * @param User $user
     *
     * @return Essais
     */
    public function addUser(User $user)
    {
        $this->users[] = $user;

        return $this;
    }

    /**
     * Remove user
     *
     * @param User $user
     */
    public function removeUser(User $user)
    {
        $this->users->removeElement($user);
    }

    /**
     * Get users
     *
     * @return Collection
     */
    public function getUsers()
    {
        return $this->users;
    }

    /**
     * Add service
     *
     * @param Service $service
     *
     * @return Essais
     */
    public function addService(Service $service)
    {
        $this->services[] = $service;

        return $this;
    }

    /**
     * Remove service
     *
     * @param Service $service
     */
    public function removeService(Service $service)
    {
        $this->services->removeElement($service);
    }

    /**
     * Get services
     *
     * @return Collection
     */
    public function getServices()
    {
        return $this->services;
    }

    /**
     * Add document
     *
     * @param DocumentEssai $documentEssai
     *
     * @return Essais
     */
    public function addDocument(DocumentEssai $documentEssai)
    {
        $this->documents[] = $documentEssai;
        $documentEssai->setEssai($this);

        return $this;
    }

    /**
     * Remove document
     *
     * @param DocumentEssai $documentEssai
     */
    public function removeDocument(DocumentEssai $documentEssai)
    {
        $this->documents->removeElement($documentEssai);
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
}

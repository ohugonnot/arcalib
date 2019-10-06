<?php

namespace AppBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Medecin
 *
 * @ORM\Table(name="medecin")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\MedecinRepository")
 */
class Medecin
{
    /**
     * @var int
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="Nom", type="string", length=100)
     */
    private $nom;

    /**
     * @var string
     * @ORM\Column(name="Prenom", type="string", length=100,nullable=true)
     */
    private $prenom;

    /**
     * @var string
     * @ORM\Column(name="Dect", type="string", length=30, nullable=true)
     */
    private $dect;

    /**
     * @var string
     * @ORM\Column(name="Portable", type="string", length=100, nullable=true)
     */
    private $portable;

    /**
     * @var string
     * @ORM\Column(name="Note", type="text", nullable=true)
     */
    private $note;

    /**
     * @var string
     * @ORM\Column(name="SecNom", type="string", length=255, nullable=true)
     */
    private $secNom;

    /**
     * @var string
     * @ORM\Column(name="SecTel", type="string", length=100, nullable=true)
     */
    private $secTel;

    /**
     * @var int
     * @ORM\Column(name="NumSiret", type="string", length=100,  nullable=true)
     */
    private $numSiret;

    /**
     * @var int
     * @ORM\Column(name="NumSigaps", type="string", length=100,  nullable=true)
     */
    private $numSigaps;

    /**
     * @var int
     * @ORM\Column(name="NumOrdre", type="string", length=100,  nullable=true)
     */
    private $numOrdre;

    /**
     * @var int
     * @ORM\Column(name="NumRpps", type="string", length=100, nullable=true)
     */
    private $numRpps;

    /**
     * @var int
     * @ORM\Column(name="Email", type="string", length=255, nullable=true)
     */
    private $email;

    /**
     * @var DateTime
     * @ORM\Column(name="dateEntre", type="date", nullable=true)
     */
    private $dateEntre;

    /**
     * @var DateTime
     * @ORM\Column(name="dateSortie", type="date", nullable=true)
     */
    private $dateSortie;

    /**
     * @var string
     * @ORM\Column(name="merri", type="string", length=20,  nullable=true)
     */
    private $merri;

    //************************************************** variable de Liens******************************

    /**
     * @ORM\OneToMany(targetEntity="Essais", mappedBy="medecin")
     */
    private $essais;

    // Ici la relation avec le patient: un medecin est lié a plusieurs patients

    /**
     * @ORM\OneToMany(targetEntity="Patient", mappedBy="medecin")
     */
    private $patients;

    // Ici la relation avec l'inclusion': un medecin est lié a plusieurs inclusion

    /**
     * @ORM\OneToMany(targetEntity="Inclusion", mappedBy="medecin")
     */
    private $inclusions;

    /**
     * @ORM\ManyToOne(targetEntity="Service", inversedBy="medecins")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $service;

    /**
     * @var User
     * @ORM\OneToOne(targetEntity="User", cascade={"persist"}, mappedBy="medecin")
     */
    private $user;

    //************************************************** Constructor******************************

    /**
     * Constructor
     */
    public function __construct()
    {
        //  Au départ mes inclusions sont une collection vide 
        $this->patients = new ArrayCollection();
        $this->inclusions = new ArrayCollection();
        $this->essais = new ArrayCollection();
    }

    //************************************************** get et set******************************

    /**
     * Get id
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get dect
     * @return string
     */
    public function getDect()
    {
        return $this->dect;
    }

    /**
     * Set dect
     * @param string $dect
     * @return medecin
     */
    public function setDect($dect)
    {
        $this->dect = $dect;

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
     * @return Medecin
     */
    public function setPortable($portable)
    {
        $this->portable = $portable;

        return $this;
    }

    /**
     * Get note
     * @return string
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * Set note
     * @param string $note
     * @return medecin
     */
    public function setNote($note)
    {
        $this->note = $note;

        return $this;
    }

    /**
     * Get secNom
     * @return string
     */
    public function getSecNom()
    {
        return $this->secNom;
    }

    /**
     * Set secNom
     * @param string $secNom
     * @return medecin
     */
    public function setSecNom($secNom)
    {
        $this->secNom = $secNom;

        return $this;
    }

    /**
     * Get secTel
     * @return string
     */
    public function getSecTel()
    {
        return $this->secTel;
    }

    /**
     * Set secTel
     * @param string $secTel
     * @return medecin
     */
    public function setSecTel($secTel)
    {
        $this->secTel = $secTel;

        return $this;
    }

    /**
     * Get numSiret
     * @return int
     */
    public function getNumSiret()
    {
        return $this->numSiret;
    }

    /**
     * Set numSiret
     * @param integer $numSiret
     * @return medecin
     */
    public function setNumSiret($numSiret)
    {
        $this->numSiret = $numSiret;

        return $this;
    }

    /**
     * Get numSigaps
     * @return int
     */
    public function getNumSigaps()
    {
        return $this->numSigaps;
    }

    /**
     * Set numSigaps
     * @param integer $numSigaps
     * @return medecin
     */
    public function setNumSigaps($numSigaps)
    {
        $this->numSigaps = $numSigaps;

        return $this;
    }

    /**
     * Get numOrdre
     * @return integer
     */
    public function getNumOrdre()
    {
        return $this->numOrdre;
    }

    /**
     * Set numOrdre
     * @param integer $numOrdre
     * @return Medecin
     */
    public function setNumOrdre($numOrdre)
    {
        $this->numOrdre = $numOrdre;

        return $this;
    }

    /**
     * Get numRpps
     * @return integer
     */
    public function getNumRpps()
    {
        return $this->numRpps;
    }

    /**
     * Set numRpps
     * @param integer $numRpps
     * @return Medecin
     */
    public function setNumRpps($numRpps)
    {
        $this->numRpps = $numRpps;

        return $this;
    }

    /**
     * Add patient
     * @param Patient $patient
     * @return medecin
     */
    public function addPatient(Patient $patient)
    {
        $this->patients[] = $patient;
        $patient->setMedecin($this);

        return $this;
    }

    /**
     * Remove patient
     * @param Patient $patient
     */
    public function removePatient(Patient $patient)
    {
        $this->patients->removeElement($patient);
        $patient->setMedecin(null);
    }

    /**
     * Get patients
     * @return Collection
     */
    public function getPatients()
    {
        return $this->patients;
    }

    /**
     * Add inclusion
     * @param Inclusion $inclusion
     * @return Medecin
     */
    public function addInclusion(Inclusion $inclusion)
    {
        $this->inclusions[] = $inclusion;
        $inclusion->setMedecin($this);

        return $this;
    }

    //************************************************** get et set** de liens****************************

    /**
     * Remove inclusion
     * @param Inclusion $inclusion
     */
    public function removeInclusion(Inclusion $inclusion)
    {
        $this->inclusions->removeElement($inclusion);
        $inclusion->setMedecin(null);

    }

    /**
     * Get inclusions
     * @return Collection
     */
    public function getInclusions()
    {
        return $this->inclusions;
    }

    public function NomPrenom()
    {
        return $this->nom . ' ' . $this->prenom;
    }

    /**
     * Add essai
     * @param Essais $essai
     * @return Medecin
     */
    public function addEssai(Essais $essai)
    {
        $this->essais[] = $essai;
        $essai->setMedecin($this);
        return $this;
    }

    /**
     * Remove essai
     * @param Essais $essai
     */
    public function removeEssai(Essais $essai)
    {
        $this->essais->removeElement($essai);
        $essai->setMedecin(null);
    }

    /**
     * Get essais
     * @return Collection
     */
    public function getEssais()
    {
        return $this->essais;
    }

    /**
     * Get service
     * @return Service
     */
    public function getService()
    {
        return $this->service;
    }

    /**
     * Set service
     * @param Service $service
     * @return Medecin
     */
    public function setService(Service $service = null)
    {
        $this->service = $service;

        return $this;
    }

    /**
     * Get email
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    /**
     * Set email
     * @param string $email
     * @return Medecin
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get dateEntre
     * @return DateTime
     */
    public function getDateEntre()
    {
        return $this->dateEntre;
    }

    /**
     * Set dateEntre
     * @param DateTime $dateEntre
     * @return Medecin
     */
    public function setDateEntre($dateEntre)
    {
        $this->dateEntre = $dateEntre;

        return $this;
    }
    //************************************************** get et set de liens*****************************

    /**
     * Get dateSortie
     * @return DateTime
     */
    public function getDateSortie()
    {
        return $this->dateSortie;
    }

    /**
     * Set dateSortie
     * @param DateTime $dateSortie
     * @return Medecin
     */
    public function setDateSortie($dateSortie)
    {
        $this->dateSortie = $dateSortie;

        return $this;
    }

    /**
     * Get merri
     * @return string
     */
    public function getMerri()
    {
        return $this->merri;
    }

    /**
     * Set merri
     * @param string $merri
     * @return Medecin
     */
    public function setMerri($merri)
    {
        $this->merri = $merri;

        return $this;
    }

    public function getNomPrenom() : string
    {
        return $this->getNom() . ' ' . $this->getPrenom();
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
     * @return medecin
     */
    public function setNom($nom)
    {
        $this->nom = strtoupper($nom);

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
     * @return Medecin
     */
    public function setPrenom($prenom)
    {
        $this->prenom = ucfirst(strtolower($prenom));

        return $this;
    }

    /**
     * @return User|null
     */
    public function getUser(): ?User
    {
        return $this->user;
    }

    /**
     * @param User $user
     * @return Medecin
     */
    public function setUser(?User $user): Medecin
    {
        $this->user = $user;
        return $this;
    }
}

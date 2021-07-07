<?php

namespace AppBundle\Entity;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Arc
 *
 * @ORM\Table(name="arc")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ArcRepository")
 */
class Arc
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
     * @ORM\Column(name="NomArc", type="string", length=50)
     */
    private $nomArc;

    /**
     * @var string
     * @ORM\Column(name="PrenomArc", type="string", length=50, nullable=true)
     */
    private $prenomArc;

    /**
     * @var DateTime
     * @ORM\Column(name="DatIn", type="date", nullable=true)
     */
    private $datIn;

    /**
     * @var DateTime
     * @ORM\Column(name="DatOut", type="date", nullable=true)
     */
    private $datOut;

    /**
     * @var string
     * @ORM\Column(name="IniArc", type="string", length=10, nullable=true)
     */
    private $iniArc;

    /**
     * @var string
     * @ORM\Column(name="Dect", type="string", length=20, nullable=true)
     */
    private $dect;

    /**
     * @var string
     * @ORM\Column(name="Tel", type="string", length=20, nullable=true)
     */
    private $tel;

    /**
     * @var string
     * @ORM\Column(name="Mail", type="string", length=100, nullable=true)
     */
    private $mail;

    /**
     * @var string
     * @ORM\Column(name="PwArc", type="string", length=20, nullable=true)
     */
    private $pwArc;

    /**
     * @var string
     * @ORM\Column(name="Droit", type="string", length=20, nullable=true)
     */
    private $droit;

    // -------------------------Ici les relations 1 arc, many inclusions/ Essais/ Visites-----------------------
    /**
     * @ORM\OneToMany(targetEntity="Inclusion", mappedBy="arc")
     */
    private $inclusions; // un arc est lié a plusieurs inclusions

    /**
     * @ORM\OneToMany(targetEntity="Essais", mappedBy="arc")
     */
    private $essais; // un arc est lié a plusieurs essias

    /**
     * @ORM\OneToMany(targetEntity="Visite", mappedBy="arc")
     */
    private $visites; // un arc est lié a plusieurs visites

    /**
     * @ORM\ManyToOne(targetEntity="Service", inversedBy="arcs")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $service;

    /**
     * @var User
     * @ORM\OneToOne(targetEntity="User", cascade={"persist"}, mappedBy="arc")
     */
    private $user;

    // -------------------------------------------------Constructor------------------------------------------

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->inclusions = new ArrayCollection();
        $this->essais = new ArrayCollection();
        $this->visites = new ArrayCollection();
    }

// -------------------------------------------------Ici les GET et SET------------------------------------------

    /**
     * Get id
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get nomArc
     * @return string
     */
    public function getNomArc()
    {
        return $this->nomArc;
    }

    /**
     * Set nomArc
     * @param string $nomArc
     * @return Arc
     */
    public function setNomArc($nomArc)
    {
        $this->nomArc = strtoupper($nomArc);

        return $this;
    }

    /**
     * Get PrenomArc
     * @return string
     */
    public function getPrenomArc()
    {
        return $this->prenomArc;
    }

    /**
     * Set PrenomArc
     * @param string $prenomArc
     * @return Arc
     */
    public function setPrenomArc($prenomArc)
    {
        $this->prenomArc = ucfirst(strtolower($prenomArc));

        return $this;
    }

    /**
     * Get datIn
     * @return DateTime
     */
    public function getDatIn()
    {
        return $this->datIn;
    }

    /**
     * Set datIn
     * @param DateTime $datIn
     * @return Arc
     */
    public function setDatIn($datIn)
    {
        $this->datIn = $datIn;

        return $this;
    }

    /**
     * Get datOut
     * @return DateTime
     */
    public function getDatOut()
    {
        return $this->datOut;
    }

    /**
     * Set datOut
     * @param DateTime $datOut
     * @return Arc
     */
    public function setDatOut($datOut)
    {
        $this->datOut = $datOut;

        return $this;
    }

    /**
     * Get iniArc
     * @return string
     */
    public function getIniArc()
    {
        return $this->iniArc;
    }

    /**
     * Set iniArc
     * @param string $iniArc
     * @return Arc
     */
    public function setIniArc($iniArc)
    {
        $this->iniArc = $iniArc;

        return $this;
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
     * @return Arc
     */
    public function setDect($dect)
    {
        $this->dect = $dect;

        return $this;
    }

    /**
     * Get tel
     * @return string
     */
    public function getTel()
    {
        return $this->tel;
    }

    /**
     * Set tel
     * @param string $tel
     * @return Arc
     */
    public function setTel($tel)
    {
        $this->tel = $tel;

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
     * @return Arc
     */
    public function setMail($mail)
    {
        $this->mail = $mail;

        return $this;
    }

    /**
     * Get pwArc
     * @return string
     */
    public function getPwArc()
    {
        return $this->pwArc;
    }

    /**
     * Set pwArc
     * @param string $pwArc
     * @return Arc
     */
    public function setPwArc($pwArc)
    {
        $this->pwArc = $pwArc;

        return $this;
    }

    /**
     * Get droit
     * @return string
     */
    public function getDroit()
    {
        return $this->droit;
    }

    /**
     * Set droit
     * @param string $droit
     * @return Arc
     */
    public function setDroit($droit)
    {
        $this->droit = $droit;

        return $this;
    }

    //----------------------------------------------------GET et SET de liens---------------------------------

    /**
     * Add inclusion
     * @param Inclusion $inclusion
     * @return Arc
     */
    public function addInclusion(Inclusion $inclusion)
    {
        $this->inclusions[] = $inclusion;
        $inclusion->setArc($this);
        return $this;
    }

    /**
     * Remove inclusion
     * @param Inclusion $inclusion
     */
    public function removeInclusion(Inclusion $inclusion)
    {
        $this->inclusions->removeElement($inclusion);
        $inclusion->setArc(null);
    }

    /**
     * Get inclusions
     * @return Collection
     */
    public function getInclusions()
    {
        return $this->inclusions;
    }

//------------------

    /**
     * Add essai
     * @param Essais $essai
     * @return Arc
     */
    public function addEssai(Essais $essai)
    {
        $this->essais[] = $essai;
        $essai->setArc($this);

        return $this;
    }

    /**
     * Remove essai
     * @param Essais $essai
     */
    public function removeEssai(Essais $essai)
    {
        $this->essais->removeElement($essai);
        $essai->setArc(null);
    }

    /**
     * Get essais
     * @return Collection
     */
    public function getEssais()
    {
        return $this->essais;
    }

//------------------

    /**
     * Add visite
     * @param Visite $visite
     * @return Arc
     */
    public function addVisite(Visite $visite)
    {
        $this->visites[] = $visite;
        $visite->setArc($this);
        return $this;
    }

    /**
     * Remove visite
     * @param Visite $visite
     */
    public function removeVisite(Visite $visite)
    {
        $this->visites->removeElement($visite);
        $visite->setArc(null);
    }

    /**
     * Get visites
     * @return Collection
     */
    public function getVisites()
    {
        return $this->visites;
    }

//---------------MO service---

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
     * @return Arc
     */
    public function setService(Service $service = null)
    {
        $this->service = $service;

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
     * @return Arc
     */
    public function setUser(?User $user): Arc
    {
        $this->user = $user;
        return $this;
    }

    public function getNomPrenom() : string
    {
        return $this->getNomArc() . ' ' . $this->getPrenomArc();
    }
}

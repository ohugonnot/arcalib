<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * LibCim10
 *
 * @ORM\Table(name="LibCim10")
 * @ORM\Table(indexes={@ORM\Index(name="cim10code", columns={"cim10code"})})
 * @ORM\Table(indexes={@ORM\Index(name="libCourt", columns={"libCourt"})})
 * @ORM\Table(indexes={@ORM\Index(name="libLong", columns={"libLong"})})
 * @ORM\Entity(repositoryClass="AppBundle\Repository\LibCim10Repository")
 */
class LibCim10
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
     * @ORM\Column(name="cim10code", type="string", length=20, nullable=true)
     */
    private $cim10code;

    /**
     * @var string
     * @ORM\Column(name="libCourt", type="string", length=100, nullable=true)
     */
    private $libCourt;

    /**
     * @var string
     * @ORM\Column(name="libLong", type="string", length=255, nullable=true)
     */
    private $libLong;

    /**
     * @var bool
     * @ORM\Column(name="utile", type="boolean", nullable=true)
     */
    private $utile;

//---------------------------variable de lien-----------------------------------------------
    //  Ici le lien 1 cim 10 vers n patient
    /**
     * @ORM\OneToMany(targetEntity="Patient", mappedBy="libCim10")
     */
    private $patients;

//----------------------------------------------------------------------------------------------

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->patients = new ArrayCollection();

    }
//-------------------------------Get et Set---------------------------------------------

    /**
     * Get id
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get cim10code
     * @return string
     */
    public function getCim10code()
    {
        return $this->cim10code;
    }

    /**
     * Set cim10code
     * @param string $cim10code
     * @return LibCim10
     */
    public function setCim10code($cim10code)
    {
        $this->cim10code = $cim10code;

        return $this;
    }

    /**
     * Get libCourt
     * @return string
     */
    public function getLibCourt()
    {
        return $this->libCourt;
    }

    /**
     * Set libCourt
     * @param string $libCourt
     * @return LibCim10
     */
    public function setLibCourt($libCourt)
    {
        $this->libCourt = $libCourt;

        return $this;
    }

    /**
     * Get libLong
     * @return string
     */
    public function getLibLong()
    {
        return $this->libLong;
    }

    /**
     * Set libLong
     * @param string $libLong
     * @return LibCim10
     */
    public function setLibLong($libLong)
    {
        $this->libLong = $libLong;

        return $this;
    }

    /**
     * Get utile
     * @return bool
     */
    public function getUtile()
    {
        return $this->utile;
    }

    /**
     * Set utile
     * @param boolean $utile
     * @return LibCim10
     */
    public function setUtile($utile)
    {
        $this->utile = $utile;

        return $this;
    }

//-------------------------------Liens---------------------------------------------

    /**
     * Add patient
     * @param Patient $patient
     * @return LibCim10
     */
    public function addPatient(Patient $patient)
    {
        $this->patients[] = $patient;
        $patient->setLibCim10($this);
        return $this;
    }

    /**
     * Remove patient
     * @param Patient $patient
     */
    public function removePatient(Patient $patient)
    {
        $this->patients->removeElement($patient);
        $patient->setLibCim10(null);
    }

    /**
     * Get patients
     * @return Collection
     */
    public function getPatients()
    {
        return $this->patients;
    }
}

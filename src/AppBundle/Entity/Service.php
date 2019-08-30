<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * Service
 *
 * @ORM\Table(name="service", indexes={
 *      @ORM\Index(name="nom_idx", columns={"nom"}),
 * })
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ServiceRepository")
 */
class Service
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="nom", type="string", length=150, nullable=true)
     */
    private $nom;
// -------------------------------------------Variables de Liens----------------------------

    /**
     * @ORM\OneToMany(targetEntity="Medecin", mappedBy="service")
     */
    private $medecins;

    /**
     * @ORM\OneToMany(targetEntity="Arc", mappedBy="service")
     */
    private $arcs;

    /**
     * @ORM\OneToMany(targetEntity="Inclusion", mappedBy="service")
     */
    private $inclusions; // un patient est lié a plusieurs inclusions

    /**
     * @ORM\ManyToMany(targetEntity="Essais", mappedBy="services", cascade={"persist"}, fetch="EXTRA_LAZY"))
     */
    private $essais;

// -------------------------------------------GET et SET----------------------------

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->medecins = new ArrayCollection();
        $this->arcs = new ArrayCollection();
        $this->inclusions = new ArrayCollection();
        $this->essais = new ArrayCollection();
    }

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
     * Get nom
     *
     * @return string
     */
    public function getNom()
    {
        return $this->nom;
    }


    // -------------------------------------------CONSTRUCT----------------------------

    /**
     * Set nom
     *
     * @param string $nom
     *
     * @return Service
     */
    public function setNom($nom)
    {
        $this->nom = $nom;

        return $this;
    }



    // -------------------------------------------medecins----------------------------

    /**
     * Add medecin
     *
     * @param Medecin $medecin
     *
     * @return Service
     */
    public function addMedecin(Medecin $medecin)
    {
        $this->medecins[] = $medecin;
        $medecin->setService($this);
        return $this;
    }

    /**
     * Remove medecin
     *
     * @param Medecin $medecin
     */
    public function removeMedecin(Medecin $medecin)
    {
        $this->medecins->removeElement($medecin);
        $medecin->setService(null);
    }

    /**
     * Get medecins
     *
     * @return Collection
     */
    public function getMedecins()
    {
        return $this->medecins;
    }
// -------------------------------------------arc----------------------------

    /**
     * Add arc
     *
     * @param Arc $arc
     *
     * @return Service
     */
    public function addArc(Arc $arc)
    {
        $this->arcs[] = $arc;
        $arc->setService($this);
        return $this;
    }

    /**
     * Remove arc
     *
     * @param Arc $arc
     */
    public function removeArc(Arc $arc)
    {
        $this->arcs->removeElement($arc);
        $arc->setService(null);
    }

    /**
     * Get arcs
     *
     * @return Collection
     */
    public function getArcs()
    {
        return $this->arcs;
    }

    /**
     * Add inclusion
     *
     * @param Inclusion $inclusion
     *
     * @return Service
     */
    public function addInclusion(Inclusion $inclusion)
    {
        $this->inclusions[] = $inclusion;
        $inclusion->setservice($this);
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
        $inclusion->setservice(null);
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
     * Add essai
     *
     * @param Essais $essai
     *
     * @return Service
     */
    public function addEssai(Essais $essai)
    {
        $this->essais[] = $essai;

        return $this;
    }

    /**
     * Remove essai
     *
     * @param Essais $essai
     */
    public function removeEssai(Essais $essai)
    {
        $this->essais->removeElement($essai);
    }

    /**
     * Get essais
     *
     * @return Collection
     */
    public function getEssais()
    {
        return $this->essais;
    }
}

<?php

namespace AppBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TraitementRepository")
 * @ORM\Table(name="traitement")
 */
class Traitement
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="date")
     */
    private $attributionAt;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $priseAt;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $traitement;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $numLot;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $peremptionAt;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $nombre;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $notes;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $verificateur;

    /**
     * @ORM\Column(type="boolean")
     */
    private $retour = false;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $retourAt;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $traitementRetour;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $numLotRetour;

    /**
     * @ORM\Column(type="integer", nullable=true)
     */
    private $nombreRetour;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $notesRetour;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $verificateurRetour;

    /**
     * @ORM\ManyToOne(targetEntity="inclusion", inversedBy="traitements")
     */
    private $inclusion;

    /**
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return DateTime|null
     */
    public function getAttributionAt(): ?DateTime
    {
        return $this->attributionAt;
    }

    /**
     * @param DateTime|null $attributionAt
     * @return Traitement
     */
    public function setAttributionAt(?DateTime $attributionAt)
    {
        $this->attributionAt = $attributionAt;
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getPriseAt(): ?DateTime
    {
        return $this->priseAt;
    }

    /**
     * @param DateTime|null $priseAt
     * @return Traitement
     */
    public function setPriseAt(?DateTime $priseAt)
    {
        $this->priseAt = $priseAt;
        return $this;
    }

    public function getTraitement()
    {
        return $this->traitement;
    }

    public function setTraitement($traitement)
    {
        $this->traitement = $traitement;
        return $this;
    }

    public function getNumLot()
    {
        return $this->numLot;
    }

    public function setNumLot($numLot)
    {
        $this->numLot = $numLot;
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getPeremptionAt(): ?DateTime
    {
        return $this->peremptionAt;
    }

    /**
     * @param DateTime|null $peremptionAt
     * @return Traitement
     */
    public function setPeremptionAt(?DateTime $peremptionAt)
    {
        $this->peremptionAt = $peremptionAt;
        return $this;
    }

    public function getNombre()
    {
        return $this->nombre;
    }

    public function setNombre($nombre)
    {
        $this->nombre = $nombre;
        return $this;
    }

    public function getNotes()
    {
        return $this->notes;
    }

    public function setNotes($notes)
    {
        $this->notes = $notes;
        return $this;
    }

    public function getVerificateur()
    {
        return $this->verificateur;
    }

    public function setVerificateur($verificateur)
    {
        $this->verificateur = $verificateur;
        return $this;
    }

    public function getRetour()
    {
        return $this->retour;
    }

    public function setRetour($retour)
    {
        $this->retour = $retour;
        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getRetourAt(): ?DateTime
    {
        return $this->retourAt;
    }

    /**
     * @param DateTime|null $retourAt
     * @return Traitement
     */
    public function setRetourAt(?DateTime $retourAt)
    {
        $this->retourAt = $retourAt;
        return $this;
    }

    public function getTraitementRetour()
    {
        return $this->traitementRetour;
    }

    public function setTraitementRetour($traitementRetour)
    {
        $this->traitementRetour = $traitementRetour;
        return $this;
    }

    public function getNumLotRetour()
    {
        return $this->numLotRetour;
    }

    public function setNumLotRetour($numLotRetour)
    {
        $this->numLotRetour = $numLotRetour;
        return $this;
    }

    public function getNombreRetour()
    {
        return $this->nombreRetour;
    }

    public function setNombreRetour($nombreRetour)
    {
        $this->nombreRetour = $nombreRetour;
        return $this;
    }

    public function getNotesRetour()
    {
        return $this->notesRetour;
    }

    public function setNotesRetour($notesRetour)
    {
        $this->notesRetour = $notesRetour;
        return $this;
    }

    public function getVerificateurRetour()
    {
        return $this->verificateurRetour;
    }

    public function setVerificateurRetour($verificateurRetour)
    {
        $this->verificateurRetour = $verificateurRetour;
        return $this;
    }

    /**
     * @param Inclusion $inclusion
     * @return $this
     */
    public function setInclusion(Inclusion $inclusion)
    {
        $this->inclusion = $inclusion;
        return $this;
    }

    /**
     * @return Inclusion
     */
    public function getInclusion()
    {
        return $this->inclusion;
    }

}
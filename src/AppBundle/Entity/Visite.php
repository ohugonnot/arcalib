<?php

namespace AppBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * Visite
 *
 * @ORM\Table(name="visite")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\VisiteRepository")
 */
class Visite
{

    const SCREEN = "Screen";
    const INCLUSION = 'Inclusion';
    const INITIATION = "Initiation";
    const RANDOMISATION = 'Randomisation';
    const SUIVI = "Suivi";
    const NON_PROGRAMMEE = 'Non programmée';
    const EI = "EI";
    const FIN_ETUDE = "Fin d'étude";
    const UNIQUE = 'Unique';
    const MONITORAGE = "Monitorage";
    const AUTRE = 'Autre';

    CONST TYPE = [
        'Screen' => self::SCREEN,
        'Inclusion' => self::INCLUSION,
        'Initiation' => self::INITIATION,
        'Randomisation' => self::RANDOMISATION,
        'Suivi' => self::SUIVI,
        'Non programmée' => self::NON_PROGRAMMEE,
        'EI' => self::EI,
        "Fin d'étude" => self::FIN_ETUDE,
        'Unique' => self::UNIQUE,
        'Monitorage' => self::MONITORAGE,
        'Autre' => self::AUTRE,

    ];

    const FAITE = "Faite";
    const NON_FAITE = 'Non faite';
    const PREVUE_CONFIRMEE = "Prévue confirmée";
    const PREVUE_THEORIQUE = 'Prévue théorique';
    const CONTACT_TEL = "Contact tel";
    const SUR_DOSSIER = 'Sur dossier';

    CONST STATUT = [
        'Faite' => self::FAITE,
        'Non faite' => self::NON_FAITE,
        'Prévue confirmée' => self::PREVUE_CONFIRMEE,
        'Prévue théorique' => self::PREVUE_THEORIQUE,
        'Contact tel' => self::CONTACT_TEL,
        'Sur dossier' => self::SUR_DOSSIER,
        'Autre' => self::AUTRE,
    ];

    /**
     * @var int
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var DateTime
     * @ORM\Column(name="date", type="datetime", nullable=true)
     */
    private $date;

    /**
     * @var DateTime
     * @ORM\Column(name="date_fin", type="datetime", nullable=true)
     */
    private $date_fin;

    /**
     * @var string
     * @ORM\Column(name="type", type="string", length=40, nullable=true)
     */
    private $type;

    /**
     * @var string
     * @ORM\Column(name="calendar", type="string", length=30, nullable=true)
     */
    private $calendar;

    /**
     * @var string
     * @ORM\Column(name="statut", type="string", length=100, nullable=true)
     */
    private $statut;

    /**
     * @var bool
     * @ORM\Column(name="fact", type="boolean", nullable=true)
     */
    private $fact;

    /**
     * @var string
     * @ORM\Column(name="numfact", type="string", length=30, nullable=true)
     */
    private $numfact;

    /**
     * @var string
     * @ORM\Column(name="note", type="text", nullable=true)
     */
    private $note;
//****************************************************************************************Jointures**************************************************************

// Lien one inclusion a many visites
    /**
     * @ORM\ManyToOne(targetEntity="Inclusion", inversedBy="visites")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $inclusion;

// Lien one arc a many visites
    /**
     * @ORM\ManyToOne(targetEntity="Arc", inversedBy="visites")
     * @ORM\JoinColumn(nullable=true , onDelete="SET NULL")
     */
    private $arc;

//****************************************************************************************GET et SET**************************************************************

    /**
     * Get id
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get date
     * @return DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set date
     * @param DateTime $date
     * @return Visite
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date
     * @return DateTime
     */
    public function getDateFin()
    {
        return $this->date_fin;
    }

    /**
     * Set date
     * @param DateTime $date_fin
     * @return Visite
     */
    public function setDateFin($date_fin)
    {
        $this->date_fin = $date_fin;

        return $this;
    }

    /**
     * Get type
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set type
     * @param string $type
     * @return Visite
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get calendar
     * @return string
     */
    public function getCalendar()
    {
        return $this->calendar;
    }

    /**
     * Set calendar
     * @param string $calendar
     * @return Visite
     */
    public function setCalendar($calendar)
    {
        $this->calendar = $calendar;

        return $this;
    }

    /**
     * Get statut
     * @return string
     */
    public function getStatut()
    {
        return $this->statut;
    }

    /**
     * Set statut
     * @param string $statut
     * @return Visite
     */
    public function setStatut($statut)
    {
        $this->statut = $statut;

        return $this;
    }

    /**
     * Get fact
     * @return boolean
     */
    public function getFact()
    {
        return $this->fact;
    }

    /**
     * Set fact
     * @param boolean $fact
     * @return Visite
     */
    public function setFact($fact)
    {
        $this->fact = $fact;

        return $this;
    }

    /**
     * Get numfact
     * @return boolean
     */
    public function getNumfact()
    {
        return $this->numfact;
    }

    /**
     * Set numfact
     * @param boolean $numfact
     * @return Visite
     */
    public function setNumfact($numfact)
    {
        $this->numfact = $numfact;

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
     * @return Visite
     */
    public function setNote($note)
    {
        $this->note = $note;

        return $this;
    }

// **************************Lien autres tables***************************************************

    /**
     * Get inclusion
     * @return Inclusion
     */
    public function getInclusion()
    {
        return $this->inclusion;
    }

    /**
     * Set inclusion
     * @param Inclusion $inclusion
     * @return Visite
     */

    public function setInclusion(Inclusion $inclusion = null)
    {
        $this->inclusion = $inclusion;

        return $this;
    }

    /**
     * Get arc
     * @return Arc
     */
    public function getArc()
    {
        return $this->arc;
    }

    /**
     * Set arc
     * @param Arc $arc
     * @return Visite
     */
    public function setArc(Arc $arc = null)
    {
        $this->arc = $arc;

        return $this;
    }

}

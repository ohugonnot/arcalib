<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Todo
 *
 * @ORM\Table(name="todo")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TodoRepository")
 */
class Todo
{

    const INFORMATION = "Information";
    const A_FAIRE = 'A faire';
    const A_SURVEILLER = "A surveiller";
    const PRIORITAIRE = 'Prioritaire';

    const IMPORTANCE = [
        "Information" => self::INFORMATION,
        "A faire" => self::INFORMATION,
        "A surveiller" => self::INFORMATION,
        "Prioritaire" => self::INFORMATION,
    ];

    const EN_COURS = "En cours";
    const RESOLU_AVEC_REMARQUES = "Résolu avec remarques";
    const RESOLU = "Résolu";

    const NIVEAU_RESOLUTION = [
        "A faire" => self::A_FAIRE,
        "En cours" => self::EN_COURS,
        "Résolu avec remarques" => self::RESOLU_AVEC_REMARQUES,
        "Résolu" => self::RESOLU,
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
     * @var \DateTime
     *
     * @ORM\Column(name="createdAt", type="date", nullable=true)
     */
    private $createdAt;


    /**
     * @var string
     *
     * @ORM\Column(name="titre", type="string", length=255, nullable=true)
     */
    private $titre;


    /**
     * @var string
     *
     * @ORM\Column(name="texte", type="text", nullable=true)
     */
    private $texte;

    /**
     * @var string
     *
     * @ORM\Column(name="resolution", type="text", nullable=true)
     */
    private $resolution;


    /**
     * @var \DateTime
     *
     * @ORM\Column(name="dateFin", type="date", nullable=true)
     */
    private $dateFin;


    /**
     * @var string
     *
     * @ORM\Column(name="importance", type="string", length=30, nullable=true)
     */
    private $importance;


    /**
     * @var bool
     *
     * @ORM\Column(name="alerte", type="boolean", nullable=true)
     */
    private $alerte;


    /**
     * @var \DateTime
     * @Assert\Expression(
     *     "this.getDateFin() >= this.getDateAlerte() or this.getDateAlerte() == null",
     *     message="La date d'alerte doit être antérieur à la date de l'échéance"
     * )
     * @Assert\Expression(
     *     "(this.getAlerte() and this.getDateAlerte() != null) or !this.getAlerte()",
     *     message="Si la cache alerte est coché il faut une date"
     * )
     * @ORM\Column(name="dateAlerte", type="date", nullable=true)
     */
    private $dateAlerte;


    /**
     * @ORM\ManyToOne(targetEntity="User", inversedBy="todoAuteurs")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $auteur;


    /**
     * @Assert\Count(
     *      min = 1,
     *      minMessage = "Vous devez choisir au moins un déstinataire",
     * )
     * @ORM\ManyToMany(targetEntity="User", inversedBy="todoDestinataires", fetch="EXTRA_LAZY"))
     * @ORM\JoinTable(name="users_todos")
     */
    private $destinataires;


    /**
     * @var string
     * @ORM\Column(name="niveauResolution", type="string", length=30, nullable=true)
     */
    private $niveauResolution;

// get et set..............................................................................

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->destinataires = new ArrayCollection();
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
     * Get createdAt
     *
     * @return \DateTime
     */
    public function getCreatedAt()
    {
        return $this->createdAt;
    }

    /**
     * Set createdAt
     *
     * @param \DateTime $createdAt
     *
     * @return Todo
     */
    public function setCreatedAt($createdAt)
    {
        $this->createdAt = $createdAt;

        return $this;
    }

    /**
     * Get importance
     *
     * @return string
     */
    public function getImportance()
    {
        return $this->importance;
    }

    /**
     * Set importance
     *
     * @param string $importance
     *
     * @return Todo
     */
    public function setImportance($importance)
    {
        $this->importance = $importance;

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
     * @return Todo
     */
    public function setTitre($titre)
    {
        $this->titre = $titre;

        return $this;
    }

    /**
     * Get texte
     *
     * @return string
     */
    public function getTexte()
    {
        return $this->texte;
    }

    /**
     * Set texte
     *
     * @param string $texte
     *
     * @return Todo
     */
    public function setTexte($texte)
    {
        $this->texte = $texte;

        return $this;
    }

    /**
     * Get resolution
     *
     * @return string
     */
    public function getResolution()
    {
        return $this->resolution;
    }

    /**
     * Set texte
     *
     * @param string $resolution
     *
     * @return Todo
     */
    public function setResolution($resolution)
    {
        $this->resolution = $resolution;

        return $this;
    }

    /**
     * Get niveauResolution
     *
     * @return string
     */
    public function getNiveauResolution()
    {
        return $this->niveauResolution;
    }

    /**
     * Set niveauResolution
     *
     * @param string $niveauResolution
     *
     * @return Todo
     */
    public function setNiveauResolution($niveauResolution)
    {
        $this->niveauResolution = $niveauResolution;

        return $this;
    }

    /**
     * Get alerte
     *
     * @return bool
     */
    public function getAlerte()
    {
        return $this->alerte;
    }

    /**
     * Set alerte
     *
     * @param boolean $alerte
     *
     * @return Todo
     */
    public function setAlerte($alerte)
    {
        $this->alerte = $alerte;

        return $this;
    }

    /**
     * Get dateAlerte
     *
     * @return \DateTime
     */
    public function getDateAlerte()
    {
        return $this->dateAlerte;
    }

    // Constructor..............................................................................

    /**
     * Set dateAlerte
     *
     * @param \DateTime $dateAlerte
     *
     * @return Todo
     */
    public function setDateAlerte($dateAlerte)
    {
        $this->dateAlerte = $dateAlerte;

        return $this;
    }

    /**
     * Get auteur
     *
     * @return User
     */
    public function getAuteur()
    {
        return $this->auteur;
    }

    /**
     * Set auteur
     *
     * @param User $auteur
     *
     * @return Todo
     */
    public function setAuteur(User $auteur = null)
    {
        $this->auteur = $auteur;
        $auteur->addTodoAuteur($this);

        return $this;
    }

    /**
     * Add destinataire
     *
     * @param User $destinataire
     *
     * @return Todo
     */
    public function addDestinataire(User $destinataire)
    {
        $this->destinataires[] = $destinataire;

        return $this;
    }

    /**
     * Remove destinataire
     *
     * @param User $destinataire
     */
    public function removeDestinataire(User $destinataire)
    {
        $this->destinataires->removeElement($destinataire);
    }

    /**
     * Get destinataires
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getDestinataires()
    {
        return $this->destinataires;
    }

    /**
     * Get dateFin
     *
     * @return \DateTime
     */
    public function getDateFin()
    {
        return $this->dateFin;
    }

    /**
     * Set dateFin
     *
     * @param \DateTime $dateFin
     *
     * @return Todo
     */
    public function setDateFin($dateFin)
    {
        $this->dateFin = $dateFin;

        return $this;
    }
}

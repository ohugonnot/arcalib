<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Fil
 * @ORM\Table(name="fil")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\FilRepository")
 */
class Fil
{
    // Ce qui sera sauver en base
    const TYPE_ADMINISTRATIF = "Administratif";
    const TYPE_MATERIEL = "Matériel";
    const TYPE_QUESTION = "Question";
    const TYPE_MONITORAGE = "Monitorage";
    const TYPE_CONVENTION = "Convention";
    const TYPE_DIVERS = "Divers";

    // Ce qu'on pourra lire dans les selecteurs sur l'outil
    CONST TYPES = [
        'Administratif' => self::TYPE_ADMINISTRATIF,
        'Matériel' => self::TYPE_MATERIEL,
        'Question' => self::TYPE_QUESTION,
        'Monitorage' => self::TYPE_MONITORAGE,
        'Convention' => self::TYPE_CONVENTION,
        'Divers' => self::TYPE_DIVERS,
    ];

    /**
     * @var int
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string|null
     * @ORM\Column(name="type", type="string", length=255, nullable=true)
     */
    private $type;

    /**
     * @var string|null
     * @ORM\Column(name="texte", type="text", nullable=true)
     */
    private $texte;

    /**
     * @var \DateTime
     * @Assert\NotNull(message="La date ne doit pas être nulle")
     * @ORM\Column(name="date", type="datetime")
     */
    private $date;

    /**
     * @ORM\ManyToOne(targetEntity="Essais", inversedBy="fils", fetch="LAZY")
     * @ORM\JoinColumn(name="essai_id", referencedColumnName="id", nullable=true)
     */
    private $essai;

    /**
     * Get id.
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set type.
     * @param string|null $type
     * @return Fil
     */
    public function setType($type = null)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get type.
     * @return string|null
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set texte.
     * @param string|null $texte
     * @return Fil
     */
    public function setTexte($texte = null)
    {
        $this->texte = $texte;

        return $this;
    }

    /**
     * Get texte.
     * @return string|null
     */
    public function getTexte()
    {
        return $this->texte;
    }

    /**
     * Set date.
     * @param \DateTime $date
     * @return Fil
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get date.
     * @return \DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Get inclusion
     * @return Essais
     */
    public function getEssai()
    {
        return $this->essai;
    }

    /**
     * Set inclusion
     * @param Essais $essai
     * @return Fil
     */
    public function setEssai(Essais $essai = null)
    {
        $this->essai = $essai;

        return $this;
    }
}

<?php

namespace AppBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Document
 *
 * @ORM\Table(name="document_essai")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\DocumentEssaiRepository")
 */
class DocumentEssai
{
    const ADMIN = "Admin";
    const SCREEN = 'Screen';
    const DOC = 'Doc';
    const VERSIONS = 'Versions';
    const COURRIERS = 'Courriers';
    const NEWSLETTER = 'Newsletter';
    const CLOTURE = 'Cloture';
    const SUSAR = 'Susar';
    const PROC = 'Procedures';
    const AUTRE = 'Autre';

    CONST TYPE = [
        'Administratif' => self::ADMIN,
        'Screen' => self::SCREEN,
        'Documents' => self::DOC,
        'Versions' => self::VERSIONS,
        'Courriers' => self::COURRIERS,
        'Newsletter' => self::NEWSLETTER,
        'Cloture' => self::CLOTURE,
        'Susar' => self::SUSAR,
        'Procedures' => self::PROC,
        'Autre' => self::AUTRE,
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
     * @var DateTime
     *
     * @ORM\Column(name="date", type="date")
     */
    private $date;

    /**
     * @var string
     * @Assert\NotNull()
     * @ORM\Column(name="type", type="string", length=255)
     */
    private $type;

    /**
     * @var string
     *
     * @ORM\Column(name="titre", type="string", length=255, nullable=true)
     */
    private $titre;

    /**
     * @var string
     *
     * @ORM\Column(name="details", type="text", nullable=true)
     */
    private $details;

    /**
     * @var string
     *
     * @ORM\Column(name="file", type="string", length=255, nullable=true)
     */
    private $file;

    /**
     * @ORM\ManyToOne(targetEntity="Essais", inversedBy="documents")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $essai;

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
     * Get date
     *
     * @return DateTime
     */
    public function getDate()
    {
        return $this->date;
    }

    /**
     * Set date
     *
     * @param DateTime $date
     *
     * @return DocumentEssai
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get type
     *
     * @return string
     */
    public function getType()
    {
        return $this->type;
    }

    /**
     * Set type
     *
     * @param string $type
     *
     * @return DocumentEssai
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get jma
     *
     * @return string
     */
    public function getTitre()
    {
        return $this->titre;
    }

    /**
     * Set jma
     *
     * @param $titre
     * @return DocumentEssai
     */
    public function setTitre($titre)
    {
        $this->titre = $titre;

        return $this;
    }

    /**
     * Get description
     *
     * @return string
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * Set description
     *
     * @param $details
     * @return DocumentEssai
     */
    public function setDetails($details)
    {
        $this->details = $details;

        return $this;
    }

    /**
     * Get file
     *
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Set file
     *
     * @param string $file
     *
     * @return DocumentEssai
     */
    public function setFile($file)
    {
        $this->file = $file;

        return $this;
    }

    /**
     * Get inclusion
     *
     * @return Essais
     */
    public function getEssai()
    {
        return $this->essai;
    }

    /**
     * Set inclusion
     *
     * @param Essais $essai
     *
     * @return DocumentEssai
     */
    public function setEssai(Essais $essai = null)
    {
        $this->essai = $essai;

        return $this;
    }
}

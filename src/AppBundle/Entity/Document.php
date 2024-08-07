<?php

namespace AppBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

/**
 * Document
 * @ORM\Table(name="document")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\DocumentRepository")
 */
class Document
{   const HIS = 'Historique';
    const VIS_COMP = 'Visite complète';
    const CONSENTEMENT = "Consentement";
    const ATTINC = 'Fiche Inclusion';
    const CR_CST = 'CR Cst';
    const CR_HOSP = 'CR Hosp';
    const CR_OP = 'CR Opératoire';
    const RCP = 'RCP';
    const ANAPATH = 'Anapath';
    const SCANNER = 'Scanner';
    const IRM = 'IRM';
    const PET_SCAN = 'PetScan';
    const RADIO = 'Radio';
    const ECG = 'ECG';
    const BILAN_BIO = 'Bilan Bio';
    const FEP = 'FEP';
    const CHIMIO = 'Chimio';
    const ORDONNANCE = 'Ordonnance';
    const RANDO = 'Rando';
    const VISITE = 'Visite';
    const QUEST = 'Questionnaire';
    const QUERY = 'Queries';
    const SUSAR = 'Susar';
    const ECHOCOEUR = 'Echocoeur';
    const BIO_MOL = 'Biologie Moléculaire';
    const AUTRES = 'Autres';

    CONST TYPE = [
        'Historique' => self::HIS,
        'Visite complète' => self::VIS_COMP,
        'Consentement' => self::CONSENTEMENT,
        'Fiche Inclusion' => self::ATTINC,
        'CR Consultation' => self::CR_CST,
        'CR Hospitalisation' => self::CR_HOSP,
        'CR Operatoire' => self::CR_OP,
        'RCP' => self::RCP,
        'Anatomopathologie' => self::ANAPATH,
        'Scanner' => self::SCANNER,
        'IRM' => self::IRM,
        'PetScan' => self::PET_SCAN,
        'Radio' => self::RADIO,
        'ECG' => self::ECG,
        'Bilan Biologique' => self::BILAN_BIO,
        'Fiche envoi prélèvement' => self::FEP,
        'Chimiothérapie' => self::CHIMIO,
        'Ordonnance' => self::ORDONNANCE,
        'Randomisation' => self::RANDO,
        'Fiche Visite' => self::VISITE,
        'Questionnaire' => self::QUEST,
        'Queries' => self::QUERY,
        'Susar' => self::SUSAR,
        'Echocoeur' => self::ECHOCOEUR,
        'Biologie Moléculaire' => self::BIO_MOL,
        'Autres' => self::AUTRES,
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
     * @ORM\Column(name="jma", type="string", length=255, nullable=true)
     */
    private $jma;

    /**
     * @var string
     * @ORM\Column(name="description", type="text", nullable=true)
     */
    private $description;

    /**
     * @var string
     * @ORM\Column(name="auteur", type="string", length=255, nullable=true)
     */
    private $auteur;

    /**
     * @var string
     * @ORM\Column(name="file", type="string", length=255, nullable=true)
     */
    private $file;

    /**
     * @var bool
     * @ORM\Column(name="is_signer", type="boolean")
     */
    private $signer = false;

    /**
     * @var bool
     * @ORM\Column(name="is_archive", type="boolean")
     */
    private $archive = false;

    /**
     * @var DateTime|null
     * @ORM\Column(name="date_signature", type="date", nullable=true)
     */
    private $dateSignature;

    /**
     * @var User|null
     * @ORM\ManyToOne(targetEntity="User", inversedBy="documentSignatures")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $signerBy;

    /**
     * @ORM\ManyToOne(targetEntity="Inclusion", inversedBy="documents")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $inclusion;

    /**
     * Get id
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return bool
     */
    public function isSigner(): bool
    {
        return $this->signer;
    }

    /**
     * @param bool $signer
     * @return Document
     */
    public function setSigner(bool $signer): Document
    {
        $this->signer = $signer;
        return $this;
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
     * @return Document
     */
    public function setDate($date)
    {
        $this->date = $date;

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
     * @return Document
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get jma
     * @return string
     */
    public function getJma()
    {
        return $this->jma;
    }

    /**
     * Set jma
     * @param string $jma
     * @return Document
     */
    public function setJma($jma)
    {
        $this->jma = $jma;

        return $this;
    }

    /**
     * Get description
     * @return string
     */
    public function getDescription()
    {
        return $this->description;
    }

    /**
     * Set description
     * @param string $description
     * @return Document
     */
    public function setDescription($description)
    {
        $this->description = $description;

        return $this;
    }

    /**
     * Get auteur
     * @return string
     */
    public function getAuteur()
    {
        return $this->auteur;
    }

    /**
     * Set auteur
     * @param string $auteur
     * @return Document
     */
    public function setAuteur($auteur)
    {
        $this->auteur = $auteur;

        return $this;
    }

    /**
     * Get file
     * @return string
     */
    public function getFile()
    {
        return $this->file;
    }

    /**
     * Set file
     * @param string $file
     * @return Document
     */
    public function setFile($file)
    {
        $this->file = $file;

        return $this;
    }

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
     * @return Document
     */
    public function setInclusion(Inclusion $inclusion = null)
    {
        $this->inclusion = $inclusion;

        return $this;
    }

    /**
     * @return User|null
     */
    public function getSignerBy(): ?User
    {
        return $this->signerBy;
    }

    /**
     * @param User|null $signerBy
     * @return Document
     */
    public function setSignerBy(?User $signerBy): Document
    {
        $this->signerBy = $signerBy;

        return $this;
    }

    /**
     * @return DateTime|null
     */
    public function getDateSignature(): ?DateTime
    {
        return $this->dateSignature;
    }

    /**
     * @param DateTime|null $dateSignature
     * @return Document
     */
    public function setDateSignature(?DateTime $dateSignature): Document
    {
        $this->dateSignature = $dateSignature;
        return $this;
    }

    /**
     * @return bool
     */
    public function isArchive(): bool
    {
        return $this->archive;
    }

    /**
     * @param bool $archive
     * @return Document
     */
    public function setArchive(bool $archive): Document
    {
        $this->archive = $archive;
        return $this;
    }
}

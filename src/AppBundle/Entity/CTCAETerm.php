<?php


namespace AppBundle\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="ctcaeterm")
 */
class CTCAETerm
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string")
     */
    private $nom;

    /**
     * @ORM\Column(type="bigint", nullable=true)
     */
    private $code;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $definition;

    /**
     * @var CTCAESoc
     * @ORM\ManyToOne(targetEntity="CTCAESoc", inversedBy="terms", cascade={"persist"})
     */
    private $soc;

    /**
     * @var ArrayCollection|CTCAEGrade[]
     * @ORM\OneToMany(targetEntity="CTCAEGrade", mappedBy="term", cascade={"persist"})
     * @ORM\OrderBy({"grade" = "ASC"})
     */
    private $grades;

    /**
     * @var ArrayCollection|EI[]
     * @ORM\OneToMany(targetEntity="EI", mappedBy="term", cascade={"persist"})
     */
    private $eis;

    public function __construct()
    {
        $this->grades = new ArrayCollection();
        $this->eis = new ArrayCollection();
    }

    /**
     * @return EI[]|ArrayCollection
     */
    public function getEis()
    {
        return $this->eis;
    }

    /**
     * @param EI $ei
     * @return $this
     */
    public function addEi(EI $ei)
    {
        if (!$this->eis->contains($ei)) {
            $this->eis[] = $ei;
            $ei->setTerm($this);
        }
        return $this;
    }

    /**
     * @param EI $ei
     * @return $this
     */
    public function removeEi(EI $ei)
    {
        if ($this->eis->contains($ei)) {
            $this->eis->removeElement($ei);
            $ei->setTerm(null);
        }
        return $this;
    }

    /**
     * @return CTCAEGrade[]|ArrayCollection
     */
    public function getGrades()
    {
        return $this->grades;
    }

    /**
     * @param CTCAEGrade $grade
     * @return $this
     */
    public function addGrade(CTCAEGrade $grade)
    {
        if (!$this->grades->contains($grade)) {
            $this->grades[] = $grade;
            $grade->setTerm($this);
        }
        return $this;
    }

    /**
     * @param CTCAEGrade $grade
     * @return $this
     */
    public function removeGrade(CTCAEGrade $grade)
    {
        if ($this->grades->contains($grade)) {
            $this->grades->removeElement($grade);
            $grade->setTerm(null);
        }
        return $this;
    }


    public function getId(): int
    {
        return $this->id;
    }

    public function getNom(): string
    {
        return $this->nom;
    }

    public function getCode(): ?int
    {
        return $this->code;
    }

    public function getDefinition(): ?string
    {
        return $this->definition;
    }

    public function setNom(string $nom): CTCAETerm
    {
        $this->nom = $nom;
        return $this;
    }

    public function setCode(?int $code): CTCAETerm
    {
        $this->code = $code;
        return $this;
    }

    public function setDefinition($definition): CTCAETerm
    {
        $this->definition = $definition;
        return $this;
    }

    public function getSoc(): ?CTCAESoc
    {
        return $this->soc;
    }

    public function setSoc(?CTCAESoc $soc): ?CTCAETerm
    {
        $this->soc = $soc;
        return $this;
    }
}
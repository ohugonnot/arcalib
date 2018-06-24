<?php


namespace AppBundle\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="ctcaegrade")
 */
class CTCAEGrade
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
    private $grade;

    /**
     * @ORM\Column(type="string")
     */
    private $nom;

    /**
     * @var CTCAETerm
     * @ORM\ManyToOne(targetEntity="AppBundle\Entity\CTCAETerm", inversedBy="grades", cascade={"persist"})
     */
    private $term;

    /**
     * @var ArrayCollection|EI[]
     * @ORM\OneToMany(targetEntity="EI", mappedBy="grade", cascade={"persist"})
     */
    private $eis;

    public function __construct()
    {
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
            $ei->setGrade($this);
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
            $ei->setGrade(null);
        }
        return $this;
    }

    /**
     * @return CTCAETerm|null
     */
    public function getTerm(): ?CTCAETerm
    {
        return $this->term;
    }

    /**
     * @param CTCAETerm|null $term
     * @return CTCAEGrade|null
     */
    public function setTerm(?CTCAETerm $term): ?CTCAEGrade
    {
        $this->term = $term;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * @return mixed
     */
    public function getGrade()
    {
        return $this->grade;
    }

    /**
     * @param mixed $grade
     * @return CTCAEGrade
     */
    public function setGrade($grade)
    {
        $this->grade = $grade;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * @param mixed $nom
     * @return CTCAEGrade
     */
    public function setNom($nom)
    {
        $this->nom = $nom;
        return $this;
    }
}
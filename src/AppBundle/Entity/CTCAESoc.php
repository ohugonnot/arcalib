<?php


namespace AppBundle\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="ctcaesoc")
 */
class CTCAESoc
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
     * @var ArrayCollection|CTCAETerm[]
     * @ORM\OneToMany(targetEntity="CTCAETerm", mappedBy="soc", cascade={"persist"})
     */
    private $terms;

    /**
     * @var ArrayCollection|EI[]
     * @ORM\OneToMany(targetEntity="EI", mappedBy="soc", cascade={"persist"})
     */
    private $eis;

    public function __construct()
    {
        $this->terms = new ArrayCollection();
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
            $ei->setSoc($this);
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
            $ei->setSoc(null);
        }
        return $this;
    }

    /**
     * @return CTCAETerm[]|ArrayCollection
     */
    public function getTerms()
    {
        return $this->terms;
    }

    /**
     * @param CTCAETerm $term
     * @return $this
     */
    public function addTerm(CTCAETerm $term)
    {
        if(!$this->terms->contains($term)) {
            $this->terms[] = $term;
            $term->setSoc($this);
        }
        return $this;
    }

    /**
     * @param CTCAETerm $term
     * @return $this
     */
    public function removeTerm(CTCAETerm $term)
    {
        if($this->terms->contains($term)) {
            $this->terms->removeElement($term);
            $term->setSoc(null);
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

    public function setNom(string $nom): CTCAESoc
    {
        $this->nom = $nom;
        return $this;
    }
}
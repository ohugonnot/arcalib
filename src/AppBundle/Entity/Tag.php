<?php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use JMS\Serializer\Annotation as Serializer;

/**
 * Tag
 *
 * @ORM\Table(name="tag")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\TagRepository")
 */
class Tag
{
    /**
     * @var int
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @Serializer\Groups({"protocole"})
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="nom", type="string", length=255, unique=true)
     * @Serializer\Groups({"protocole"})
     */
    private $nom;

    /**
     * @var string
     * @ORM\Column(name="classe", type="string", length=255, nullable=true)
     * @Serializer\Groups({"protocole"})
     */
    private $classe;

    // =========================variable de liens*************************************

    //1 essai est lié a n mots clefs et 1 mot clef est lié a n essais

    /**
     * @ORM\ManyToMany(targetEntity="Essais", inversedBy="tags", cascade={"persist"}, fetch="EXTRA_LAZY") )
     * @ORM\JoinTable(name="essais_tags")
     */
    private $essais;

// =========================Get et Set*************************************

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->essais = new ArrayCollection();
    }

    /**
     * Get id
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get nom
     * @return string
     */
    public function getNom()
    {
        return $this->nom;
    }

    /**
     * Set nom
     * @param string $nom
     * @return Tag
     */
    public function setNom($nom)
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * Get classe
     * @return string
     */
    public function getClasse()
    {
        return $this->classe;
    }

// =========================constructor et Get & Set de liens*************************************

    /**
     * Set classe
     * @param string $classe
     * @return Tag
     */
    public function setClasse($classe)
    {
        $this->classe = $classe;

        return $this;
    }

    /**
     * Add essai
     * @param Essais $essai
     * @return Tag
     */
    public function addEssai(Essais $essai)
    {
        $this->essais[] = $essai;

        return $this;
    }

    /**
     * Remove essai
     * @param Essais $essai
     */
    public function removeEssai(Essais $essai)
    {
        $this->essais->removeElement($essai);
    }

    /**
     * Get essais
     * @return Collection
     */
    public function getEssais()
    {
        return $this->essais;
    }
}

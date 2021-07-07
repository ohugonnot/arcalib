<?php
// src/AppBundle/Entity/User.php

namespace AppBundle\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use FOS\UserBundle\Model\User as BaseUser;

/**
 * @ORM\Entity
 * @ORM\Table(name="fos_user")
 */
class User extends BaseUser
{

    const ALL_PROTOCOLE = "all";
    const NO_PROTOCOLE = "np";
    const ONLY_CHOSEN_PROTOCOLE = "ocp";

    const RULES_PROTOCOLE = [
        "Tous les protocoles" => self::ALL_PROTOCOLE,
        "Aucun protocole" => self::NO_PROTOCOLE,
        "Choisir les protocoles autorisés" => self::ONLY_CHOSEN_PROTOCOLE,
    ];

    /**
     * @ORM\Id
     * @ORM\Column(type="integer")
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    protected $id;

    /**
     * @var string
     * @ORM\Column(name="Nom", type="string", length=100)
     */
    private $nom;

    /**
     * @var ?string
     * @ORM\Column(name="Prenom", type="string", length=100, nullable=true)
     */
    private $prenom;

    /**
     * @ORM\OneToMany(targetEntity="Log", mappedBy="user", cascade={"all"})
     */
    private $logs;

    /**
     * @ORM\OneToMany(targetEntity="Todo", mappedBy="auteur", cascade={"all"})
     */
    private $todoAuteurs;

    /**
     * @var string
     * @ORM\Column(name="rulesProtocole", type="string", length=255, nullable=true)
     */
    private $rulesProtocole;

    /**
     * @ORM\ManyToMany(targetEntity="Todo", mappedBy="destinataires", cascade={"persist"}, fetch="EXTRA_LAZY"))
     */
    private $todoDestinataires;

    /**
     * @ORM\ManyToMany(targetEntity="Essais", inversedBy="users", cascade={"persist"}, fetch="EXTRA_LAZY"))
     * @ORM\JoinTable(name="users_essais")
     */
    private $essais;

    /**
     * @var ArrayCollection|EI[]
     * @ORM\ManyToMany(targetEntity="EI", mappedBy="users", cascade={"persist"}, fetch="EXTRA_LAZY")
     */
    private $eis;

    /**
     * @var Medecin
     * @ORM\OneToOne(targetEntity="Medecin", cascade={"all"}, inversedBy="user")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $medecin;

    /**
     * @var Arc
     * @ORM\OneToOne(targetEntity="Arc", cascade={"all"}, inversedBy="user")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $arc;

    /**
     * @ORM\OneToMany(targetEntity="Document", mappedBy="signerBy", cascade={"all"})
     * @ORM\OrderBy({"date" = "ASC"})
     */
    private $documentSignatures;

    public function __construct()
    {
        parent::__construct();
        $this->todoDestinataires = new ArrayCollection();
        $this->essais = new ArrayCollection();
        $this->logs = new ArrayCollection();
        $this->todoAuteurs = new ArrayCollection();
        $this->eis = new ArrayCollection();
        $this->documentSignatures = new ArrayCollection();
    }


    /**
     * @return string
     */
    public function getNom(): ?string
    {
        return $this->nom;
    }

    /**
     * @param string $nom
     * @return User
     */
    public function setNom(string $nom): User
    {
        $this->nom = strtoupper($nom);
        return $this;
    }

    /**
     * @return string
     */
    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    /**
     * @param string $prenom
     * @return User
     */
    public function setPrenom(?string $prenom): User
    {
        $this->prenom = ucfirst(strtolower($prenom));
        return $this;
    }

    /**
     * @return EI[]|ArrayCollection
     */
    public function geteis()
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
            $ei->addUser($this);
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
            $ei->removeUser($this);
        }
        return $this;
    }

    /**
     * Add log
     * @param Log $log
     * @return User
     */
    public function addLog(Log $log)
    {
        $this->logs[] = $log;
        $log->setUser($this);
        return $this;
    }

    /**
     * Remove log
     * @param Log $log
     */
    public function removeLog(Log $log)
    {
        $this->logs->removeElement($log);
        $log->setUser(null);
    }

    /**
     * Get logs
     * @return Collection
     */
    public function getLogs()
    {
        return $this->logs;
    }

    /**
     * Add todoAuteur
     * @param Todo $todoAuteur
     * @return User
     */
    public function addTodoAuteur(Todo $todoAuteur)
    {
        $this->todoAuteurs[] = $todoAuteur;

        return $this;
    }

    /**
     * Remove todoAuteur
     * @param Todo $todoAuteur
     */
    public function removeTodoAuteur(Todo $todoAuteur)
    {
        $this->todoAuteurs->removeElement($todoAuteur);
        $todoAuteur->setAuteur(null);
    }

    /**
     * Get todoAuteurs
     * @return Collection
     */
    public function getTodoAuteurs()
    {
        return $this->todoAuteurs;
    }

    /**
     * Add todoDestinataire
     * @param Todo $todoDestinataire
     * @return User
     */
    public function addTodoDestinataire(Todo $todoDestinataire)
    {
        $this->todoDestinataires[] = $todoDestinataire;

        return $this;
    }

    /**
     * Remove todoDestinataire
     * @param Todo $todoDestinataire
     */
    public function removeTodoDestinataire(Todo $todoDestinataire)
    {
        $this->todoDestinataires->removeElement($todoDestinataire);
    }

    /**
     * Get todoDestinataires
     * @return Collection
     */
    public function getTodoDestinataires()
    {
        return $this->todoDestinataires;
    }

    /**
     * Add essai
     * @param Essais $essai
     * @return User
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

    /**
     * Get rulesProtocole
     * @return string
     */
    public function getRulesProtocole()
    {
        return $this->rulesProtocole;
    }

    /**
     * Set rulesProtocole
     * @param string $rulesProtocole
     * @return User
     */
    public function setRulesProtocole($rulesProtocole)
    {
        $this->rulesProtocole = $rulesProtocole;

        return $this;
    }

    /**
     * @return Medecin|null
     */
    public function getMedecin(): ?Medecin
    {
        return $this->medecin;
    }

    /**
     * @param Medecin $medecin
     * @return User
     */
    public function setMedecin(?Medecin $medecin): User
    {
        $this->medecin = $medecin;
         if ($medecin)
            $medecin->setUser($this);
        return $this;
    }

    /**
     * @return Arc|null
     */
    public function getArc(): ?Arc
    {
        return $this->arc;
    }

    /**
     * @param Arc $arc
     * @return User
     */
    public function setArc(?Arc $arc): User
    {
        $this->arc = $arc;
        if ($arc)
            $arc->setUser($this);
        return $this;
    }


    /**
     * Add document
     * @param Document $document
     * @return User
     */
    public function addDocument(Document $document)
    {
        $this->documentSignatures[] = $document;
        $document->setSignerBy($this);

        return $this;
    }

    /**
     * Remove document
     * @param Document $document
     */
    public function removeDocument(Document $document)
    {
        $this->documentSignatures->removeElement($document);
    }

    /**
     * Get documents
     * @return Collection|Document[]
     */
    public function getDocuments()
    {
        return $this->documentSignatures;
    }
}

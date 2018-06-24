<?php


namespace AppBundle\Entity;


use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="ei")
 */
class EI
{

    const EI = "EI";
    const EIG = "EIG";

    const HOSPITALISATION = "Hospitalisation/ Prolongation d'hospitalisation";
    const MIS_EN_DANGER_VIE = "Mis en danger de la vie";
    const INCAPACITE_HANDICAP = "Incapacité ou handicap important ou durable";
    const ANOMALIE_MALFORMATION = "Anomalie ou malformation congénitale";
    const DECES = "Décès";

    const RESOLU = "Résolu";
    const RESOLU_AVEC_SEQUELLES = "Résolu avec sequelles";
    const STABILISE = "Stabilisé";
    const EN_COURS = "En cours";
    const EN_COURS_RESOLUTION = "En cours de résolution";

    const DEFAILLANCE_MULTIVISCERALE = "Défaillance multiviscerale";
    const DEFAILLANCE_MONO_ORGANE = "Défaillance mono organe";
    const CHOC_HEMORRAGIQUE = "Choc hemorragique";
    const CHOC_ANAPHYLACTIQUE = "Choc anaphylactique";
    const CHOC_SCEPTIQUE = "Choc sceptique";
    const AUTRE = "Autre";

    const OUI = "Oui";
    const NON = "Non";
    const NSP = "NSP";
    const NA = "Na";

    const FERME = "Fermé";
    const OUVERT = "Ouvert";
    const ALERTE = "Alerte";

    const TYPE = [
        'EI' => self::EI,
        'EIG' => self::EIG,
    ];

    const SI_EIG = [
        "Hospitalisation/ Prolongation d'hospitalisation" => self::EI,
        "Mis en danger de la vie" => self::MIS_EN_DANGER_VIE,
        "Incapacité ou handicap important ou durable" => self::INCAPACITE_HANDICAP,
        "Anomalie ou malformation congénitale" => self::ANOMALIE_MALFORMATION,
        "Décès" => self::DECES,
    ];

    const EVOLUTION = [
        "Résolu" => self::RESOLU,
        "Résolu avec sequelles" => self::RESOLU_AVEC_SEQUELLES,
        "Stabilisé" => self::STABILISE,
        "En cours" => self::EN_COURS,
        "En cours de résolution" => self::EN_COURS_RESOLUTION,
        "Décès" => self::DECES,
    ];

    const SI_DECES = [
        "Défaillance multiviscerale" => self::DEFAILLANCE_MULTIVISCERALE,
        "Défaillance mono organe" => self::DEFAILLANCE_MONO_ORGANE,
        "Choc hemorragique" => self::CHOC_HEMORRAGIQUE,
        "Choc anaphylactique" => self::CHOC_ANAPHYLACTIQUE,
        "Choc sceptique" => self::CHOC_SCEPTIQUE,
        "Autre" => self::AUTRE,
    ];

    const SURCOUTS = [
        "Oui" => self::OUI,
        "Non" => self::NON,
        "NSP" => self::NSP,
        "Na" => self::NA,
    ];

    const SUIVI = [
        "Fermé" => self::FERME,
        "Ouvert" => self::OUVERT,
        "Alerte" => self::ALERTE,
    ];

    /**
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $type;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $siEIG;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $debutAt;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $diagnostic;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $evolution;

    /**
     * @ORM\Column(type="date", nullable=true)
     */
    private $finAt;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $siDeces;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $texteDC;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $details;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $surcouts;

    /**
     * @ORM\Column(type="string")
     */
    private $suivi;


    /**
     * @var ArrayCollection|User[]
     * @ORM\ManyToMany(targetEntity="User", inversedBy="eis", cascade={"persist"}, fetch="EXTRA_LAZY")
     * @ORM\JoinTable(name="users_eis")
     */
    private $users;

    /**
     * @var CTCAESoc
     * @ORM\ManyToOne(targetEntity="CTCAESoc", inversedBy="eis")
     */
    private $soc;

    /**
     * @var CTCAETerm
     * @ORM\ManyToOne(targetEntity="CTCAETerm", inversedBy="eis")
     */
    private $term;

    /**
     * @var CTCAEGrade
     * @ORM\ManyToOne(targetEntity="CTCAEGrade", inversedBy="eis")
     */
    private $grade;

    /**
     * @var Inclusion
     * @ORM\ManyToOne(targetEntity="Inclusion", inversedBy="eis")
     */
    private $inclusion;

    public function __construct()
    {
        $this->users = new ArrayCollection();
    }

    /**
     * @return Inclusion
     */
    public function getInclusion(): ?Inclusion
    {
        return $this->inclusion;
    }

    /**
     * @param Inclusion|null $inclusion
     * @return EI
     */
    public function setInclusion(?Inclusion $inclusion): EI
    {
        $this->inclusion = $inclusion;
        return $this;
    }

    /**
     * @return CTCAETerm
     */
    public function getTerm(): ?CTCAETerm
    {
        return $this->term;
    }

    /**
     * @param CTCAETerm $term
     * @return EI
     */
    public function setTerm(?CTCAETerm $term): EI
    {
        $this->term = $term;
        return $this;
    }

    /**
     * @return CTCAEGrade
     */
    public function getGrade(): ?CTCAEGrade
    {
        return $this->grade;
    }

    /**
     * @param CTCAEGrade $grade
     * @return EI
     */
    public function setGrade(?CTCAEGrade $grade): EI
    {
        $this->grade = $grade;
        return $this;
    }

    /**
     * @return CTCAESoc
     */
    public function getSoc(): ?CTCAESoc
    {
        return $this->soc;
    }

    /**
     * @param CTCAESoc $soc
     * @return EI
     */
    public function setSoc(?CTCAESoc $soc): EI
    {
        $this->soc = $soc;
        return $this;
    }

    /**
     * @return ArrayCollection|User[]
     */
    public function getUsers(): ArrayCollection
    {
        return $this->users;
    }

    /**
     * @param User $user
     * @return $this
     */
    public function addUser(User $user)
    {
        if (!$this->users->contains($user)) {
            $this->users[] = $user;
            $user->addEi($this);
        }
        return $this;
    }

    /**
     * @param User $user
     * @return $this
     */
    public function removeUser(User $user)
    {
        if ($this->users->contains($user)) {
            $this->users->removeElement($user);
            $user->removeEi($this);
        }
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
    public function getType()
    {
        return $this->type;
    }

    /**
     * @param mixed $type
     * @return EI
     */
    public function setType($type)
    {
        $this->type = $type;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSiEIG()
    {
        return $this->siEIG;
    }

    /**
     * @param mixed $siEIG
     * @return EI
     */
    public function setSiEIG($siEIG)
    {
        $this->siEIG = $siEIG;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDebutAt()
    {
        return $this->debutAt;
    }

    /**
     * @param mixed $debutAt
     * @return EI
     */
    public function setDebutAt($debutAt)
    {
        $this->debutAt = $debutAt;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDiagnostic()
    {
        return $this->diagnostic;
    }

    /**
     * @param mixed $diagnostic
     * @return EI
     */
    public function setDiagnostic($diagnostic)
    {
        $this->diagnostic = $diagnostic;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getEvolution()
    {
        return $this->evolution;
    }

    /**
     * @param mixed $evolution
     * @return EI
     */
    public function setEvolution($evolution)
    {
        $this->evolution = $evolution;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getFinAt()
    {
        return $this->finAt;
    }

    /**
     * @param mixed $finAt
     * @return EI
     */
    public function setFinAt($finAt)
    {
        $this->finAt = $finAt;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSiDeces()
    {
        return $this->siDeces;
    }

    /**
     * @param mixed $siDeces
     * @return EI
     */
    public function setSiDeces($siDeces)
    {
        $this->siDeces = $siDeces;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getTexteDC()
    {
        return $this->texteDC;
    }

    /**
     * @param mixed $texteDC
     * @return EI
     */
    public function setTexteDC($texteDC)
    {
        $this->texteDC = $texteDC;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getDetails()
    {
        return $this->details;
    }

    /**
     * @param mixed $details
     * @return EI
     */
    public function setDetails($details)
    {
        $this->details = $details;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSurcouts()
    {
        return $this->surcouts;
    }

    /**
     * @param mixed $surcouts
     * @return EI
     */
    public function setSurcouts($surcouts)
    {
        $this->surcouts = $surcouts;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getSuivi()
    {
        return $this->suivi;
    }

    /**
     * @param mixed $suivi
     * @return EI
     */
    public function setSuivi($suivi)
    {
        $this->suivi = $suivi;
        return $this;
    }
}
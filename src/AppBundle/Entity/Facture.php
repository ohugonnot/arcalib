<?php

namespace AppBundle\Entity;

use DateTime;
use Doctrine\ORM\Mapping as ORM;

/**
 * facture
 *
 * @ORM\Table(name="facture")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\factureRepository")
 */
class Facture
{

    const CREDIT = "Crédit";
    const DEBIT = 'Débit';

    CONST CREDIT_DEBIT = [
        'Crédit' => self::CREDIT,
        'Débit' => self::DEBIT,
    ];


    const TVA = "TVA";
    const NO_TVA = 'Non assujeti TVA';

    CONST TYPE = [
        'TVA' => self::TVA,
        'Non assujeti TVA' => self::NO_TVA,
    ];

    const PREPARATION = "Préparation";
    const EN_ATTENTE = 'En attente';
    const PAYE = "Payé";
    const RELANCE = 'Relance';
    const CONTENTIEUX = 'Contentieux';

    CONST STATUT = [
        'Préparation' => self::PREPARATION,
        'En attente' => self::EN_ATTENTE,
        'Payé' => self::PAYE,
        'Relance' => self::RELANCE,
        'Contentieux' => self::CONTENTIEUX,
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
     * @ORM\Column(name="date", type="date", nullable=true)
     */
    private $date;


    /**
     * @var string
     *
     * @ORM\Column(name="numero", type="string", length=20, nullable=true)
     */
    private $numero;


    /**
     * @var string
     *
     * @ORM\Column(name="numInterne", type="string", length=255, nullable=true)
     */
    private $numInterne;


//---------------------------Lien avec Essai-------------------------------
    /**
     * @ORM\ManyToOne(targetEntity="Essais", inversedBy="factures")
     * @ORM\JoinColumn(nullable=true, onDelete="SET NULL")
     */
    private $essai;

//---------------------------Lien avec Essai-------------------------------

    /**
     * @var string
     *
     * @ORM\Column(name="projet", type="string", length=255, nullable=true)
     */
    private $projet;

    /**
     * @var string
     *
     * @ORM\Column(name="creditDebit", type="string", length=255, nullable=true)
     */
    private $creditDebit;


    /**
     * @var string
     *
     * @ORM\Column(name="Payeur", type="string", length=50, nullable=true)
     */
    private $payeur;


    /**
     * @var string
     *
     * @ORM\Column(name="receveur", type="string", length=30, nullable=true)
     */
    private $receveur;


    /**
     * @var string
     *
     * @ORM\Column(name="montantHt", type="decimal", precision=8, scale=2, nullable=true)
     */
    private $montantHt;


    /**
     * @var string
     *
     * @ORM\Column(name="taxTVA", type="decimal", precision=8, scale=2, nullable=true)
     */
    private $taxTVA;


    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255, nullable=true)
     */
    private $type;


    /**
     * @var string
     *
     * @ORM\Column(name="TVA", type="decimal", precision=8, scale=2, nullable=true)
     */
    private $TVA;


    /**
     * @var string
     *
     * @ORM\Column(name="montantTtc", type="decimal", precision=8, scale=2, nullable=true)
     */
    private $montantTtc;


    /**
     * @var string
     *
     * @ORM\Column(name="statut", type="string", length=20, nullable=true)
     */
    private $statut;


    /**
     * @var DateTime
     *
     * @ORM\Column(name="dateCaisse", type="date", nullable=true)
     */
    private $dateCaisse;


    /**
     * @var string
     *
     * @ORM\Column(name="note", type="text", nullable=true)
     */
    private $note;


    /**
     * @var string
     *
     * @ORM\Column(name="responsable", type="string", length=50, nullable=true)
     */
    private $responsable;


    /**
     * @var DateTime
     *
     * @ORM\Column(name="dateEncaissement", type="date", nullable=true)
     */
    private $dateEncaissement;

    /**
     * @ORM\Column(type="string", nullable=true)
     */
    private $facture;

    public function getFacture()
    {
        return $this->facture;
    }

    public function setFacture($facture)
    {
        $this->facture = $facture;

        return $this;
    }


//--------------------------GET et SET-------------------------------

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
     * @return facture
     */
    public function setDate($date)
    {
        $this->date = $date;

        return $this;
    }

    /**
     * Get numero
     *
     * @return string
     */
    public function getNumero()
    {
        return $this->numero;
    }

    /**
     * Set numero
     *
     * @param string $numero
     *
     * @return facture
     */
    public function setNumero($numero)
    {
        $this->numero = $numero;

        return $this;
    }

    /**
     * Get montantHt
     *
     * @return string
     */
    public function getMontantHt()
    {
        return $this->montantHt;
    }

    /**
     * Set montantHt
     *
     * @param string $montantHt
     *
     * @return facture
     */
    public function setMontantHt($montantHt)
    {
        $this->montantHt = $montantHt;

        return $this;
    }

    /**
     * Get montantTtc
     *
     * @return string
     */
    public function getMontantTtc()
    {
        return $this->montantTtc;
    }

    /**
     * Set montantTtc
     *
     * @param string $montantTtc
     *
     * @return facture
     */
    public function setMontantTtc($montantTtc)
    {
        $this->montantTtc = $montantTtc;

        return $this;
    }

    /**
     * Get statut
     *
     * @return string
     */
    public function getStatut()
    {
        return $this->statut;
    }

    /**
     * Set statut
     *
     * @param string $statut
     *
     * @return facture
     */
    public function setStatut($statut)
    {
        $this->statut = $statut;

        return $this;
    }

    /**
     * Get dateCaisse
     *
     * @return DateTime
     */
    public function getDateCaisse()
    {
        return $this->dateCaisse;
    }

    /**
     * Set dateCaisse
     *
     * @param DateTime $dateCaisse
     *
     * @return facture
     */
    public function setDateCaisse($dateCaisse)
    {
        $this->dateCaisse = $dateCaisse;

        return $this;
    }

    /**
     * Get note
     *
     * @return string
     */
    public function getNote()
    {
        return $this->note;
    }

    /**
     * Set note
     *
     * @param string $note
     *
     * @return facture
     */
    public function setNote($note)
    {
        $this->note = $note;

        return $this;
    }

    /**
     * Get payeur
     *
     * @return string
     */
    public function getPayeur()
    {
        return $this->payeur;
    }

    /**
     * Set payeur
     *
     * @param string $payeur
     *
     * @return Facture
     */
    public function setPayeur($payeur)
    {
        $this->payeur = $payeur;

        return $this;
    }

    /**
     * Get receveur
     *
     * @return string
     */
    public function getReceveur()
    {
        return $this->receveur;
    }

    /**
     * Set receveur
     *
     * @param string $receveur
     *
     * @return Facture
     */
    public function setReceveur($receveur)
    {
        $this->receveur = $receveur;

        return $this;
    }

    /**
     * Get responsable
     *
     * @return string
     */
    public function getResponsable()
    {
        return $this->responsable;
    }

    /**
     * Set responsable
     *
     * @param string $responsable
     *
     * @return Facture
     */
    public function setResponsable($responsable)
    {
        $this->responsable = $responsable;

        return $this;
    }


//-------------------------------------Lien ESsai GET  SET------------------------------

    /**
     * Get essai
     *
     * @return Essais
     */
    public function getEssai()
    {
        return $this->essai;
    }

    /**
     * Set essai
     *
     * @param Essais $essai
     *
     * @return Facture
     */
    public function setEssai(Essais $essai = null)
    {
        $this->essai = $essai;

        return $this;
    }

    /**
     * Get numInterne
     *
     * @return string
     */
    public function getNumInterne()
    {
        return $this->numInterne;
    }

    /**
     * Set numInterne
     *
     * @param string $numInterne
     *
     * @return Facture
     */
    public function setNumInterne($numInterne)
    {
        $this->numInterne = $numInterne;

        return $this;
    }

    /**
     * Get taxTVA
     *
     * @return string
     */
    public function getTaxTVA()
    {
        return $this->taxTVA;
    }

    /**
     * Set taxTVA
     *
     * @param string $taxTVA
     *
     * @return Facture
     */
    public function setTaxTVA($taxTVA)
    {
        $this->taxTVA = $taxTVA;

        return $this;
    }

    /**
     * Get tVA
     *
     * @return string
     */
    public function getTVA()
    {
        return $this->TVA;
    }

    /**
     * Set tVA
     *
     * @param string $tVA
     *
     * @return Facture
     */
    public function setTVA($tVA)
    {
        $this->TVA = $tVA;

        return $this;
    }

    /**
     * Get projet
     *
     * @return string
     */
    public function getProjet()
    {
        return $this->projet;
    }

    /**
     * Set projet
     *
     * @param string $projet
     *
     * @return Facture
     */
    public function setProjet($projet)
    {
        $this->projet = $projet;

        return $this;
    }

    /**
     * Get creditDebit
     *
     * @return string
     */
    public function getCreditDebit()
    {
        return $this->creditDebit;
    }

    /**
     * Set creditDebit
     *
     * @param string $creditDebit
     *
     * @return Facture
     */
    public function setCreditDebit($creditDebit)
    {
        $this->creditDebit = $creditDebit;

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
     * @return Facture
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get dateEncaissement
     *
     * @return DateTime
     */
    public function getDateEncaissement()
    {
        return $this->dateEncaissement;
    }

    /**
     * Set dateEncaissement
     *
     * @param DateTime $dateEncaissement
     *
     * @return Facture
     */
    public function setDateEncaissement($dateEncaissement)
    {
        $this->dateEncaissement = $dateEncaissement;

        return $this;
    }
}

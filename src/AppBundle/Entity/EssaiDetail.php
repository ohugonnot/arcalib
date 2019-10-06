<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * EssaiDetail
 *
 * @ORM\Table(name="essai_detail")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\EssaiDetailRepository")
 */
class EssaiDetail
{
    /**
     * @var int
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     * @ORM\Column(name="crInc", type="text", nullable=true)
     */
    private $crInc;

    /**
     * @var string
     * @ORM\Column(name="crNonInc", type="text", nullable=true)
     */
    private $crNonInc;

    /**
     * @var string
     * @ORM\Column(name="Objectif", type="text", nullable=true)
     */
    private $objectif;

    /**
     * @var string
     * @ORM\Column(name="calendar", type="text",nullable=true)
     */
    private $calendar;

    /** ***************************************Liens One to One vers ESSAI*********************************/

    /**
     * @ORM\OneToOne(targetEntity="Essais", mappedBy="detail" ,cascade={"persist"})
     */
    private $essai;

    /*****************************************GET ET SET*********************************/

    /**
     * Get id
     * @return int
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get crInc
     * @return string
     */
    public function getCrInc()
    {
        return $this->crInc;
    }

    /**
     * Set crInc
     * @param string $crInc
     * @return EssaiDetail
     */
    public function setCrInc($crInc)
    {
        $this->crInc = $crInc;

        return $this;
    }

    /**
     * Get crNonInc
     * @return string
     */
    public function getCrNonInc()
    {
        return $this->crNonInc;
    }

    /**
     * Set crNonInc
     * @param string $crNonInc
     * @return EssaiDetail
     */
    public function setCrNonInc($crNonInc)
    {
        $this->crNonInc = $crNonInc;

        return $this;
    }

    /**
     * Get objectif
     * @return string
     */
    public function getObjectif()
    {
        return $this->objectif;
    }

    /**
     * Set objectif
     * @param string $objectif
     * @return EssaiDetail
     */
    public function setObjectif($objectif)
    {
        $this->objectif = $objectif;

        return $this;
    }

    /**
     * Get calendar
     * @return string
     */
    public function getCalendar()
    {
        return $this->calendar;
    }

    /**
     * Set calendar
     * @param string $calendar
     * @return EssaiDetail
     */
    public function setCalendar($calendar)
    {
        $this->calendar = $calendar;

        return $this;
    }

    /** ***************************************GET ET SET du lien ESSAI*********************************/

    /**
     * Get essai
     * @return Essais
     */
    public function getEssai()
    {
        return $this->essai;
    }

    /**
     * Set essai
     * @param Essais $essai
     * @return EssaiDetail
     */
    public function setEssai(Essais $essai = null)
    {
        $this->essai = $essai;

        return $this;
    }
}

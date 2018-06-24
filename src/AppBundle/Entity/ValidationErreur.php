<?php

namespace AppBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * ValidationErreur
 *
 * @ORM\Table(name="validation_erreur")
 * @ORM\Entity(repositoryClass="AppBundle\Repository\ValidationErreurRepository")
 */
class ValidationErreur
{
    /**
     * @var int
     *
     * @ORM\Column(name="id", type="integer")
     * @ORM\Id
     * @ORM\GeneratedValue(strategy="AUTO")
     */
    private $id;

    /**
     * @var string
     *
     * @ORM\Column(name="type", type="string", length=255)
     */
    private $type;

    /**
     * @var int
     *
     * @ORM\Column(name="erreur", type="integer")
     */
    private $erreur;

    /**
     * @var int
     *
     * @ORM\Column(name="entite", type="integer")
     */
    private $entite;

    /**
     * @var string
     *
     * @ORM\Column(name="message", type="text", length=255, nullable=true)
     */
    private $message;

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
     * @return ValidationErreur
     */
    public function setType($type)
    {
        $this->type = $type;

        return $this;
    }

    /**
     * Get erreur
     *
     * @return int
     */
    public function getErreur()
    {
        return $this->erreur;
    }

    /**
     * Set erreur
     *
     * @param integer $erreur
     *
     * @return ValidationErreur
     */
    public function setErreur($erreur)
    {
        $this->erreur = $erreur;

        return $this;
    }

    /**
     * Get entite
     *
     * @return int
     */
    public function getEntite()
    {
        return $this->entite;
    }

    /**
     * Set entite
     *
     * @param integer $entite
     *
     * @return ValidationErreur
     */
    public function setEntite($entite)
    {
        $this->entite = $entite;

        return $this;
    }

    /**
     * Get message
     *
     * @return string
     */
    public function getMessage()
    {
        return $this->message;
    }

    /**
     * Set message
     *
     * @param $message
     * @return string
     *
     */
    public function setMessage($message)
    {
        $this->message = $message;

        return $this;
    }
}

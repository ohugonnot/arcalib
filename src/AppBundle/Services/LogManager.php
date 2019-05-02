<?php

namespace AppBundle\Services;

use AppBundle\Entity\Log;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class LogManager
{
    private $em;

    private $tokenStorage;

    private $user;

    /**
     * LogManager constructor.
     * @param EntityManagerInterface $em
     * @param TokenStorageInterface $tokenStorage
     */
    public function __construct(EntityManagerInterface $em, TokenStorageInterface $tokenStorage)
    {
        $this->em = $em;
        $this->tokenStorage = $tokenStorage;
    }

    /**
     * Return roles.
     *
     * @param $name
     * @param $entity
     * @param $action
     * @param $info
     * @param null $changeSet
     * @return bool
     */
    public function save($name, $entity = null, $action, $info, $changeSet = null)
    {
        if ($this->tokenStorage->getToken() != null) {
            $this->user = $this->tokenStorage->getToken()->getUser() ?? null;
        }
        if ($this->user === "anon.") {
            $this->user = null;
        }

        $log = new Log();
        date_default_timezone_set('Europe/Paris');

        $log->setCreatedAt(new \DateTime)
            ->setEntity($name)
            ->setAction($action)
            ->setUser($this->user ?? null)
            ->setInfo($info)
            ->setChangeSet($changeSet);

        if ($entity != null && $entity->getId()) {
            $log->setEntityId($entity->getId());
        }

        $this->em->persist($log);
        $this->em->flush();

        return true;
    }
}
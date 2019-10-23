<?php

namespace AppBundle\Services;

use AppBundle\Entity\User;
use AppBundle\Entity\Visite;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class PatientAlerte
{
    private $em;
    private $storage;
    private $requestStack;

    /**
     * TodoAlerte constructor.
     * @param EntityManagerInterface $em
     * @param TokenStorageInterface $storage
     * @param RequestStack $requestStack
     */
    public function __construct(EntityManagerInterface $em, TokenStorageInterface $storage, RequestStack $requestStack)
    {
        $this->em = $em;
        $this->storage = $storage;
        $this->requestStack = $requestStack;
    }

    /**
     * @return array
     * @throws Exception
     */
    public function getAlertes()
    {
        $token = $this->storage->getToken();
        if (!$token)
            return [];

        $user = $token->getUser();
        if (!($user instanceof User))
            return [];

        $emVisite = $this->em->getRepository(Visite::class);
        $visiteConfirmeeTheorique = $emVisite->findConfirmeeTheoriqueDepassee($user);

        return $visiteConfirmeeTheorique;
    }
}
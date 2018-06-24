<?php

namespace AppBundle\Services;

use AppBundle\Entity\Todo;
use AppBundle\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Roles helper displays roles set in config.
 */
class TodoAlerte
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
     */
    public function getAlertes()
    {
        $token = $this->storage->getToken();
        if (!$token) {
            return [];
        }

        $user = $token->getUser();

        if (!($user instanceof User)) {
            return [];
        }

        $emTodo = $this->em->getRepository(Todo::class);
        $alertes = $emTodo->findAlertes($user);

        return $alertes;
    }

    /**
     * @return array
     */
    public function getNewTodos()
    {
        $lastVisite = $this->requestStack->getCurrentRequest()->cookies->get("lastVisite");

        if ($lastVisite) {
            $lastVisite = new \DateTime($lastVisite);
        }

        $token = $this->storage->getToken();
        if (!$token) {
            return [];
        }

        $user = $token->getUser();

        if (!($user instanceof User)) {
            return [];
        }

        $emTodo = $this->em->getRepository(Todo::class);
        $newTodos = $emTodo->findNewTodos($user, $lastVisite);

        return $newTodos;
    }
}
<?php

namespace AppBundle\EventListener;

use AppBundle\Services\LogManager;
use FOS\UserBundle\Model\UserManagerInterface;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class LoginListener
{

    private $userManager;
    private $session;
    private $log;

    /**
     * LoginListener constructor.
     * @param UserManagerInterface $userManager
     * @param LogManager $log
     * @param SessionInterface $session
     */
    public function __construct(UserManagerInterface $userManager, LogManager $log, SessionInterface $session)
    {
        $this->userManager = $userManager;
        $this->log = $log;
        $this->session = $session;
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event)
    {
        $user = $event->getAuthenticationToken()->getUser();
        $this->session->set("modalNewVisites", true);
        $this->log->save("User", null, "connexion", "L'utilisateur " . $user->getUsername() . " s'est connecté");
    }

}
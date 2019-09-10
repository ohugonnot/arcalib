<?php

namespace AppBundle\EventListener;

use AppBundle\Services\LogManager;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Logout\LogoutHandlerInterface;

class LogoutListener implements LogoutHandlerInterface
{

    private $log;

    /**
     * LogoutListener constructor.
     * @param LogManager $log
     */
    public function __construct(LogManager $log)
    {
        $this->log = $log;
    }

    /**
     * @param Request $Request
     * @param Response $Response
     * @param TokenInterface $tokenStorage
     * @throws \Exception
     */
    public function logout(Request $Request, Response $Response, TokenInterface $tokenStorage)
    {
        $this->log->save("User", null, "déconnexion", "L'utilisateur " . $tokenStorage->getUser()->getUsername() . " s'est déconnecté");
        setcookie('cc_loggedin', null, -1, '/');
        setcookie('cc_an', null, -1, '/');
        setcookie('cc_data', null, -1, '/');
        setcookie('cc_disablelastseen', null, -1, '/');
        setcookie('cc_popup', null, -1, '/');
        setcookie('cc_sound', null, -1, '/');
        setcookie('cc_state', null, -1, '/');
        $Response->headers->clearCookie('cc_data');
        $Response->send();
    }
}
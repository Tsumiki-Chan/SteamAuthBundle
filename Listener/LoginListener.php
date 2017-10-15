<?php

namespace SteamAuthBundle\Listener;

use Doctrine\ORM\EntityManager;
use SteamAuthBundle\Service\SteamUserService;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;

class LoginListener 
{
    private $userService;
    private $em;

    public function __construct(EntityManager $em, SteamUserService $userService) {
        $this->userService = $userService;
        $this->em = $em;
    }

    public function onSecurityInteractivelogin(InteractiveLoginEvent $event) {
        $user = $event->getAuthenticationToken()->getUser();

        $this->userService->updateUserEntry($user);
        
        $this->em->persist($user);
        $this->em->flush($user);
    }
}
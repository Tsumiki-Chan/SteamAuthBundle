<?php

namespace SteamAuthBundle\Listener;

use Doctrine\ORM\EntityManager;
use Monolog\Logger;
use SteamAuthBundle\Service\SteamUserService;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;
use Symfony\Component\Security\Core\Event\AuthenticationEvent;
use Symfony\Component\Security\Core\Event\AuthenticationFailureEvent;

class LoginListener 
{

    private $logger;
    private $userService;
    private $em;

    public function __construct(Logger $logger, EntityManager $em, SteamUserService $userService) {
        $this->logger = $logger;
        $this->userService = $userService;
        $this->em = $em;

        $this->logger->info("LoginListener! __construct");
    }

    public function onSecurityInteractivelogin(InteractiveLoginEvent $event) {
        $this->logger->info("LoginListener! InteractiveLoginEvent");

        $user = $event->getAuthenticationToken()->getUser();

        $userService->updateUserEntry($user);
        
        $this->em->persist($user);
        $this->em->flush($user);
    }
}
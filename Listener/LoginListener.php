<?php

namespace SteamAuthBundle\Listener;

use Doctrine\ORM\EntityManager;
use Monolog\Logger;
use SteamAuthBundle\Service\SteamUserService;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class LoginListener 
{

    public function __construct(Logger $logger, EntityManager $em, SteamUserService $userService) {
        $this->logger = $logger;
        $this->userService = $userService;
        $this->em = $em;
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event) {
        $user = $event->getAuthenticationToken()->getUser();

        $userService->updateUserEntry($user);
        
        $this->em->persist($user);
        $this->em->flush($user);
    }

}
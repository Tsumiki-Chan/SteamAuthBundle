<?php

namespace SteamAuthBundle\Listener;

class LoginListener 
{

    public function __construct(EntityManager $em, SteamUserService $userService) {
        $this->userService = $userService;
        $this->em = $em;
    }

    public function onSecurityInteractiveLogin(InteractiveLoginEvent $event) {
        $user = $event->getAuthenticationToken()->getUser();

        $userService->updateUserEntry($user);
    }

}
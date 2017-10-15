<?php
namespace SteamAuthBundle\Security\Firewall;

use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Security\Http\Firewall\ListenerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Exception\AuthenticationException;
use Symfony\Component\Security\Http\Util\TargetPathTrait;
use Symfony\Component\Security\Http\RememberMe\RememberMeServicesInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Routing\Router;
use SteamAuthBundle\Security\Token\SteamToken;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\Security\Http\Event\InteractiveLoginEvent;

class SteamListener implements ListenerInterface
{
    use TargetPathTrait;

    private $tokenStorage;
    private $authenticationManager;
    private $router;
    private $rememberMeServices;
    private $providerKey;
    private $defaultRoute;
    private $dispatcher;

    public function __construct($defaultRoute, TokenStorageInterface $tokenStorage, AuthenticationManagerInterface $authenticationManager, Router $router, EventDispatcherInterface $dispatcher)
    {
        $this->defaultRoute = $defaultRoute;
        $this->tokenStorage = $tokenStorage;
        $this->authenticationManager = $authenticationManager;
        $this->router = $router;
        $this->dispatcher = $dispatcher;
    }

    public function setProviderKey($providerKey)
    {
        $this->providerKey = $providerKey;
    }

    public function getProviderKey($providerKey)
    {
        return $this->providerKey;
    }

    public function setRememberMeServices(RememberMeServicesInterface $rememberMeServices)
    {
        $this->rememberMeServices = $rememberMeServices;
    }

    public function handle(GetResponseEvent $event)
    {
        $request = $event->getRequest();
        if ($request->get('_route') != 'login_check') {
            return;
        }

        $token = new SteamToken();
        $token->setUsername(str_replace("http://steamcommunity.com/openid/id/", "", $request->query->get('openid_claimed_id')));
        $token->setAttributes($request->query->all());

        try {
            
        } catch (AuthenticationException $failed) {
            $token = $this->tokenStorage->getToken();
            if ($token instanceof SteamToken && $this->providerKey === $token->getProviderKey()) {
                $this->tokenStorage->setToken(null);
            }
            return;
        }

        $authToken = $this->authenticationManager->authenticate($token);
        $this->tokenStorage->setToken($authToken);

        if (null !== $this->dispatcher) {
            $loginEvent = new InteractiveLoginEvent($request, $token);
            $this->dispatcher->dispatch(SecurityEvents::INTERACTIVE_LOGIN, $loginEvent);
        }

        $targetPath = $this->getTargetPath($request->getSession(), $this->providerKey);
        if ($targetPath !== null) {
            $this->removeTargetPath($request->getSession(), $this->providerKey);
        } else {
            $targetPath = $this->router->generate($this->defaultRoute);
        }

        $response = new RedirectResponse($targetPath);
        if ($this->rememberMeServices !== null) {
            $this->rememberMeServices->loginSuccess($request, $response, $token);
        }
        $event->setResponse($response);
        
        return;
    }
}

<?php

namespace App\EventSubscriber;

use App\Controller\Api\TokenAuthenticatedController;
use App\Manager\ApiKeyManagerInterface;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\HttpKernel\Event\FilterControllerEvent;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\Security\Core\Authentication\AuthenticationManagerInterface;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

class ApiTokenSubscriber implements EventSubscriberInterface
{
    /** @var ApiKeyManagerInterface */
    private $keyManager;

    /** @var TokenStorageInterface */
    private $tokenStorage;

    /** @var AuthenticationManagerInterface */
    private $providerManager;

    public function __construct(
        ApiKeyManagerInterface $keyManager,
        AuthenticationManagerInterface $providerManager,
        TokenStorageInterface $tokenStorage
    ) {
        $this->keyManager = $keyManager;
        $this->providerManager = $providerManager;
        $this->tokenStorage = $tokenStorage;
    }

    /** {@inheritdoc} */
    public static function getSubscribedEvents()
    {
        return [
            KernelEvents::CONTROLLER => 'onKernelController',
        ];
    }

    public function onKernelController(FilterControllerEvent $event): void
    {
        $controller = $event->getController();

        if (!is_array($controller))
            return;

        if (!($controller[0] instanceof TokenAuthenticatedController))
            return;

        $public = $event->getRequest()->query->get('public');
        $private = $event->getRequest()->query->get('private');

        if (is_null($public) || is_null($private))
            $this->denyAccess();

        $key = $this->keyManager->findApiKey($public);

        if (is_null($key) || !password_verify($private, $key->getPrivateKey()))
            $this->denyAccess();

        $token = $this->tokenStorage->getToken();
        $token->setUser($key->getUser());

        $this->providerManager->authenticate($token);
    }

    private function denyAccess(): void
    {
        throw new AccessDeniedHttpException('This action needs valid API keys.');
    }
}

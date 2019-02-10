<?php

namespace App\EventListener;

use App\Notification\NotificationInterface;
use Symfony\Component\HttpFoundation\Session\Flash\FlashBagInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

class NotificationListener
{
    /** @var FlashBagInterface $flashBag */
    private $flashBag;

    /** @var NotificationInterface $notification */
    private $notification;

    /** @var TokenStorageInterface $tokenStorage */
    private $tokenStorage;

    /** @var AuthorizationCheckerInterface $authorizationChecker */
    private $authorizationChecker;

    public function __construct(
        FlashBagInterface $flashBag,
        NotificationInterface $notification,
        TokenStorageInterface $tokenStorage,
        AuthorizationCheckerInterface $authorizationChecker
    ) {
        $this->flashBag = $flashBag;
        $this->notification = $notification;
        $this->tokenStorage = $tokenStorage;
        $this->authorizationChecker = $authorizationChecker;
    }

    public function onKernelRequest(GetResponseEvent $event): void
    {
        $request = $event->getRequest();
        if ($request->isXmlHttpRequest() ||
            !$this->notification->hasNotification() ||
            !$this->isUserLoggedIn() ||
            'dashboard' !== $request->get('_route')
        ) return;

        $levelName = $this->notification->getLevelName();
        if ('alert' === $levelName)
            $levelName = 'danger';

        $this->flashBag->clear();
        $this->flashBag->set($levelName, $this->notification->getMessage());
    }

    private function isUserLoggedIn(): bool
    {
        return $this->tokenStorage->getToken() && $this->authorizationChecker->isGranted('ROLE_USER');
    }
}

<?php

namespace App\EventListener;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Event\GetResponseForExceptionEvent;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;
use Symfony\Component\Security\Core\Exception\AccessDeniedException;

class AdminExceptionListener
{
    public function onKernelException(GetResponseForExceptionEvent $event): void
    {
        if (!$event->isMasterRequest())
            return;

        $request = $event->getRequest();
        $exception = $event->getException();

        // Catch 403 Access Denied and throw 404 Not Found instead
        // for security through obscurity matter
        if ($this->isAdminRoute($request) && $exception instanceof AccessDeniedException)
            throw new NotFoundHttpException();
    }

    private function isAdminRoute(Request $request): bool
    {
        return 0 === strpos($request->attributes->get('_route'), 'sonata_admin_');
    }
}

<?php

namespace App\EventListener;

use PragmaRX\Random\Random;
use Symfony\Component\HttpFoundation\Session\SessionInterface;
use Symfony\Component\HttpKernel\Event\GetResponseEvent;
use Symfony\Component\HttpKernel\HttpKernel;

class RandomScriptNameListener
{

    public function onKernelRequest(GetResponseEvent $event): void
    {
        if (!$event->isMasterRequest()) {
            return;
        }

        $request = $event->getRequest();
        $session = $request->getSession();
        $codeRoutes = [
            'dashboard',
            'site_show_code',
            'documentation_root',
        ];

        $routeName = $request->get('_route');
        
        if (!in_array($routeName, $codeRoutes)) {
            return;
        }

        if (!$session->has('scriptName')
        || ($session->has('scriptNameTime')
        && ($session->get('scriptNameTime') + 24 * 60 * 60) <= time())) {
            $session->set('scriptName', [
                'php' => (new Random())->size(4)->get(),
                'js' => (new Random())->size(4)->get(),
            ]);
            $session->set('scriptNameTime', time());
        }

        $request->attributes->set('scriptName', $session->get('scriptName'));
    }
}

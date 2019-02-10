<?php

namespace App\EventListener;

use Psr\Log\LoggerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\TokenInterface;
use Symfony\Component\Security\Http\Logout\LogoutHandlerInterface;

class LogoutRequestLoggerListener implements LogoutHandlerInterface
{
    /** @var LoggerInterface */
    private $logger;

    public function __construct(LoggerInterface $actionLogger)
    {
        $this->logger = $actionLogger;
    }

    public function logout(Request $request, Response $response, TokenInterface $token): void
    {
        $this->logger->info('logout', [ 'request_type' => 'page' ]);
    }
}

<?php

namespace App\Logger;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Invokable class which adds remote IP address and username to the log entry.
 */
class UserActionProcessor
{
    /** @var TokenStorageInterface */
    private $authTokenStorage;

    /** @var RequestStack */
    private $requestStack;

    public function __construct(
        TokenStorageInterface $authTokenStorage,
        RequestStack $requestStack
    ) {
        $this->authTokenStorage = $authTokenStorage;
        $this->requestStack = $requestStack;
    }

    public function __invoke(array $record): array
    {
        $record['extra']['username'] = $this->getUsername();
        $record['extra']['ip_address'] = $this->requestStack->getCurrentRequest()->getClientIp();
        return $record;
    }

    private function getUsername(): string
    {
        $token = $this->authTokenStorage->getToken();
        return is_null($token) ? '' : $token->getUsername();
    }
}

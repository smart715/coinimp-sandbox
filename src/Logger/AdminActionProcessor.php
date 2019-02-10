<?php

namespace App\Logger;

use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Authentication\Token\Storage\TokenStorageInterface;

/**
 * Invokable class which adds remote IP address, username and Role to the log entry.
 */
class AdminActionProcessor
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
        $record['extra']['role'] = $this->getRoles();
        return $record;
    }

    private function getUsername(): string
    {
        $token = $this->authTokenStorage->getToken();
        return is_null($token) ? '' : $token->getUsername();
    }

    private function getRoles(): string
    {
        $token = $this->authTokenStorage->getToken();
        $roles = [];
        if (!is_null($token)) {
            foreach ($token->getRoles() as $role) {
                $roles[] = $role->getRole();
            }
        }
        return implode($roles, ';');
    }
}

<?php

namespace App\Entity;


class GoogleAuthenticatorEntry
{
    /** @var int */
    private $id;

    /** @var User */
    private $user;

    /** @var string */
    private $secret;

    /** @var array */
    private $backupCodes;

    public function getId(): int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(User $user): void
    {
        $this->user = $user;
    }

    public function getSecret(): ?string
    {
        return $this->secret;
    }

    public function setSecret(string $secret): void
    {
        $this->secret = $secret;
    }

    public function getBackupCodes(): array
    {
        return $this->backupCodes ?? [];
    }

    public function setBackupCodes(array $backupCodes): void
    {
        $this->backupCodes = $backupCodes;
    }

    public function invalidateBackupCode(string $code): void
    {
        $key = array_search($code, $this->backupCodes);
        if ($key !== false)
            unset($this->backupCodes[$key]);
    }
}

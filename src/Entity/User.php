<?php

namespace App\Entity;

use FOS\UserBundle\Model\User as BaseUser;
use DateTime;
use Scheb\TwoFactorBundle\Model\BackupCodeInterface;
use Scheb\TwoFactorBundle\Model\Google\TwoFactorInterface;

class User extends BaseUser implements TwoFactorInterface, BackupCodeInterface
{
    const ROLE_API = 'ROLE_API';
    protected $id;
    private $tempEmail;
    private $key;
    protected $googleID;
    private $createdAt;

    /* @var $googleAuthenticatorEntry GoogleAuthenticatorEntry|null */
    private $googleAuthenticatorEntry;

    public function __construct()
    {
        parent::__construct();

        // to avoid empty username validation problem:
        $this->username = "empty";
        $this->createdAt = new DateTime();
        $this->roles = [static::ROLE_DEFAULT];
    }

    public function setTempEmail(string $email) :User
    {
        $this->tempEmail = $email;

        return $this;
    }

    public function getTempEmail() :string
    {
        return (string)$this->tempEmail;
    }

    public function hasTempEmail() :bool
    {
        return !empty($this->tempEmail);
    }

    public function setEmail($email) :User
    {
        parent::setEmail($email ?? '');

        // set email as username, because our application
        // uses only email to identify the user:
        $this->setUsername($email ?? '');

        return $this;
    }

    public function getCreatedAt() :DateTime
    {
        return $this->createdAt ?? new DateTime();
    }

    public function getGoogleId() :string
    {
        return $this->googleID ?? '';
    }

    public function setGoogleId(string $googleId) :User
    {
        $this->googleID = $googleId;

        return $this;
    }

    public function getApiKey() :?ApiKey
    {
        return $this->key;
    }

    public function generateApiKey() :ApiKey
    {
        if (is_null($this->getApiKey()))
            return ApiKey::fromNewUser($this);

        $key = $this->getApiKey();
        $key->generateKeys();
        return $key;
    }

    public function isAccountNonLocked() :bool
    {
        return $this->isEnabled();
    }

    public function isNotEmptyPassword(): bool
    {
        return !empty($this->getPlainPassword());
    }

    public function removeRole($role) :User
    {
        /* Overriden to not remove ROLE_USER
           as FOSUserBundle implementation doesn't
           contemplate ROLE_USER existence in db */
        return $role === static::ROLE_DEFAULT
            ? $this
            : parent::removeRole($role);
    }

    public function isGoogleAuthenticatorEnabled(): bool
    {
        return $this->googleAuthenticatorEntry !== null;
    }

    public function getGoogleAuthenticatorUsername(): string
    {
        return $this->username;
    }

    public function getGoogleAuthenticatorSecret(): string
    {
        $googleAuth = $this->googleAuthenticatorEntry;
        return $googleAuth ? $googleAuth->getSecret() : '';
    }

    public function isBackupCode(string $code): bool
    {
        $googleAuth = $this->googleAuthenticatorEntry;
        return $googleAuth ? in_array($code, $googleAuth->getBackupCodes()) : false;
    }

    public function invalidateBackupCode(string $code): void
    {
        if (null !== $this->googleAuthenticatorEntry)
            $this->googleAuthenticatorEntry->invalidateBackupCode($code);
    }

    public function getGoogleAuthenticatorBackupCodes(): array
    {
        $googleAuth = $this->googleAuthenticatorEntry;
        return $googleAuth ? $googleAuth->getBackupCodes() : [];
    }

    public function setGoogleAuthenticatorSecret(string $secret): void
    {
        $this->getGoogleAuthenticatorEntry()->setSecret($secret);
    }

    public function setGoogleAuthenticatorBackupCodes(array $codes): void
    {
        $this->getGoogleAuthenticatorEntry()->setBackupCodes($codes);
    }

    private function getGoogleAuthenticatorEntry(): GoogleAuthenticatorEntry
    {
        if (null === $this->googleAuthenticatorEntry)
            $this->googleAuthenticatorEntry = new GoogleAuthenticatorEntry;
        if ($this !== $this->googleAuthenticatorEntry->getUser())
            $this->googleAuthenticatorEntry->setUser($this);
        return $this->googleAuthenticatorEntry;
    }
}

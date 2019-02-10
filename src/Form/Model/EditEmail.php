<?php

namespace App\Form\Model;

use App\Entity\Profile;

class EditEmail
{
    /** @var Profile|string */
    private $profile;
    /** @var Profile|string */
    private $email;

    public function __construct(Profile $profile)
    {
        $this->profile = $profile;
        $this->email = $this->profile->getEmail();
    }

    public function getEmail(): string
    {
        return $this->email;
    }

    public function setEmail(?string $email): void
    {
        $this->email = $email;
    }

    public function isExistingEmail(): bool
    {
        return $this->getEmail() !== $this->profile->getEmail();
    }
}

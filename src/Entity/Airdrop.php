<?php

namespace App\Entity;

class Airdrop
{
    /** @var int */
    private $id = 0;
    /** @var string */
    private $code;
    /** @var bool */
    private $isActive = false;
    /** @var Profile | null*/
    private $profile = null ;

    public function getCode(): string
    {
        return $this->code;
    }

    public function setCode(string $code): void
    {
        $this->code = $code;
    }

    public function getIsActive(): bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): void
    {
        $this->isActive = $isActive;
    }

    public function getProfile(): Profile
    {
        return $this->profile;
    }

    public function setProfile(Profile $profile): void
    {
        $this->profile = $profile;
    }

}
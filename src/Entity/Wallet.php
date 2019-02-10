<?php

namespace App\Entity;


class Wallet
{
    private $id;
    private $profile;
    private $address = '';
    private $crypto;

    public function __construct(Profile $profile, Crypto $crypto)
    {
        $this->profile = $profile;
        $this->crypto = $crypto;
    }

    public function getAddress() :string
    {
        return $this->address;
    }

    public function setAddress(string $address): void
    {
        $this->address = $address;
    }

    public function getCrypto() :Crypto
    {
        return $this->crypto;
    }
}
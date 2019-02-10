<?php

namespace App\Form\Model;

class EditWallet
{
    /** @var string */
    private $walletAddress = '';

    public function getWalletAddress(): string
    {
        return $this->walletAddress??'';
    }

    public function setWalletAddress(?string $address): void
    {
        $this->walletAddress = $address;
    }
}

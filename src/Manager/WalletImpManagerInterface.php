<?php

namespace App\Manager;

use App\Entity\WalletImp\WalletImp;

interface WalletImpManagerInterface
{
    public function addToWallet(
        WalletImp $wallet,
        float $amount
    ): void;

    public function subFromWallet(
        WalletImp $wallet,
        float $amount
    ): void;

    public function freezeFromWallet(
        WalletImp $wallet,
        float $amount
    ): void;

    public function moveFreezeAmountToWallet(
        WalletImp $fromWallet,
        WalletImp $toWallet,
        float $amount
    ): void;

    public function moveAmountToWallet(
        WalletImp $fromWallet,
        WalletImp $toWallet,
        float $amount
    ): void;

    public function addToActualPaid(
        WalletImp $wallet,
        float $amount
    ): void;
}

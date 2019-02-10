<?php

namespace App\Manager;

use App\Entity\WalletImp\WalletImp;
use App\Exception\Wallet\NotEnoughBalanceException;

class WalletImpManager implements WalletImpManagerInterface
{
    public function addToWallet(
        WalletImp $wallet,
        float $amount
    ): void {
        $wallet->setTotalAmount($wallet->getTotalAmount() + $amount);
    }

    public function subFromWallet(
        WalletImp $wallet,
        float $amount
    ): void {

        if (false === $wallet->isAvailableAmount($amount)) {
            throw new NotEnoughBalanceException();
        }

        $wallet->setTotalAmount($wallet->getTotalAmount() - $amount);
    }

    public function freezeFromWallet(
        WalletImp $wallet,
        float $amount
    ): void {

        if (false === $wallet->isAvailableAmount($amount)) {
            throw new NotEnoughBalanceException();
        }

        $wallet->setFreezeAmount($wallet->getFreezeAmount() + $amount);
    }

    public function moveFreezeAmountToWallet(
        WalletImp $fromWallet,
        WalletImp $toWallet,
        float $amount
    ): void {
        if (false === $fromWallet->isAvailableAmount($amount, WalletImp::FREEZE_BALANCE)) {
            throw new NotEnoughBalanceException();
        }

        $fromWallet->setTotalAmount($fromWallet->getTotalAmount() - $amount);
        $fromWallet->setFreezeAmount($fromWallet->getFreezeAmount() - $amount);

        $toWallet->setTotalAmount($toWallet->getTotalAmount() + $amount);
    }

    public function moveAmountToWallet(
        WalletImp $fromWallet,
        WalletImp $toWallet,
        float $amount
    ): void {

        if (false === $fromWallet->isAvailableAmount($amount)) {
            throw new NotEnoughBalanceException();
        }

        $fromWallet->setTotalAmount($fromWallet->getTotalAmount() - $amount);
        $toWallet->setTotalAmount($toWallet->getTotalAmount() + $amount);
    }

    public function addToActualPaid(
        WalletImp $wallet,
        float $amount
    ): void {
        $wallet->setActualPaid($wallet->getActualPaid() + $amount);
    }
}

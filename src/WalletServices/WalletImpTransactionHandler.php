<?php

namespace App\WalletServices;

use App\Entity\Profile;
use App\Entity\WalletImp\WalletImpTransaction;
use App\Manager\WalletImpManagerInterface;
use App\Manager\WalletImpTransactionManagerInterface;
use Doctrine\ORM\EntityManagerInterface;

class WalletImpTransactionHandler implements WalletImpTransactionHandlerInterface
{
    /** @var EntityManagerInterface */
    private $em;

    /** @var WalletImpTransactionManagerInterface */
    private $walletTransactionManager;

    /** @var WalletImpManagerInterface */
    private $walletImpManager;

    public function __construct(
        EntityManagerInterface $em,
        WalletImpManagerInterface $walletImpManager,
        WalletImpTransactionManagerInterface $walletTransactionManager
    ) {
        $this->em = $em;
        $this->walletImpManager = $walletImpManager;
        $this->walletTransactionManager = $walletTransactionManager;
    }

    public function addToUser(
        Profile $profile,
        int $amount,
        string $name,
        ?string $description = null,
        array $data = []
    ): void {
        $wallet = $profile->getWalletImp();

        $this->createTransaction(function () use ($wallet, $amount, $name, $description, $data): void {
            $this->walletImpManager->addToWallet($wallet, $amount);
            $this->walletTransactionManager->addWalletTransaction(
                $wallet,
                WalletImpTransaction::TYPE_ADD,
                $amount,
                $name,
                $description,
                $data
            );
        });
    }

    public function subFromUser(
        Profile $profile,
        int $amount,
        string $name,
        ?string $description = null,
        array $data = []
    ): void {
        $wallet = $profile->getWalletImp();

        $this->createTransaction(function () use ($wallet, $amount, $name, $description, $data): void {
            $this->walletImpManager->subFromWallet($wallet, $amount);
            $this->walletTransactionManager->addWalletTransaction(
                $wallet,
                WalletImpTransaction::TYPE_SUB,
                $amount,
                $name,
                $description,
                $data
            );
        });
    }

    public function freezeFromUser(
        Profile $profile,
        int $amount,
        string $name,
        ?string $description = null,
        array $data = []
    ): void {
        $wallet = $profile->getWalletImp();

        $this->createTransaction(function () use ($wallet, $amount, $name, $description, $data): void {
            $this->walletImpManager->freezeFromWallet($wallet, $amount);
            $this->walletTransactionManager->addWalletTransaction(
                $wallet,
                WalletImpTransaction::TYPE_FREEZE,
                $amount,
                $name,
                $description,
                $data
            );
        });
    }

    public function moveFreezeAmountToUser(
        Profile $fromProfile,
        Profile $toProfile,
        int $amount,
        string $name,
        ?string $description = null,
        array $data = []
    ): void {
        $fromWallet = $fromProfile->getWalletImp();
        $toWallet = $toProfile->getWalletImp();

        $this->createTransaction(function () use ($fromWallet, $toWallet, $amount, $name, $description, $data): void {
            $this->walletImpManager->moveFreezeAmountToWallet($fromWallet, $toWallet, $amount);
            $this->walletTransactionManager->addWalletTransaction(
                $toWallet,
                WalletImpTransaction::TYPE_ADD,
                $amount,
                $name,
                $description,
                $data
            );
        });
    }

    public function moveAmountToUser(
        Profile $fromProfile,
        Profile $toProfile,
        int $amount,
        string $name,
        ?string $description = null,
        array $data = []
    ): void {
        $fromWallet = $fromProfile->getWalletImp();
        $toWallet = $toProfile->getWalletImp();

        $this->createTransaction(function () use ($fromWallet, $toWallet, $amount, $name, $description, $data): void {
            $this->walletImpManager->moveAmountToWallet($fromWallet, $toWallet, $amount);
            $this->walletTransactionManager->addWalletTransaction(
                $fromWallet,
                WalletImpTransaction::TYPE_SUB,
                $amount,
                $name,
                $description,
                $data
            );

            $this->walletTransactionManager->addWalletTransaction(
                $toWallet,
                WalletImpTransaction::TYPE_ADD,
                $amount,
                $name,
                $description,
                $data
            );
        });
    }

    public function addToActualPaid(
        Profile $profile,
        int $amount
    ): void {
        $wallet = $profile->getWalletImp();

        $this->createTransaction(function () use ($wallet, $amount): void {
            $this->walletImpManager->addToActualPaid($wallet, $amount);
        });
    }

    private function createTransaction(callable $action): void
    {
        $this->em->getConnection()->beginTransaction();
        try {
            $action();
            $this->em->flush();
            $this->em->getConnection()->commit();
        } catch (\Throwable $error) {
            $this->em->getConnection()->rollBack();
            throw $error;
        }
    }
}

<?php

namespace App\Entity\WalletImp;

use DateTime;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Exception\Wallet\BalanceTypeNotExistException;

class WalletImp
{
    const AVAILABLE_BALANCE = 1;
    const FREEZE_BALANCE = 2;

    const impUnitBase = 8;
    /** @var integer */
    private $id;

    /** @var integer */
    private $totalAmount = 0;

    /** @var integer */
    private $freezeAmount = 0;

    /** @var integer */
    private $actualPaid = 0;

    /** @var ArrayCollection */
    private $transactions;

    /** @var DateTime */
    private $created;

    /** @var DateTime */
    private $updated;

    public function __construct()
    {
        $this->transactions = new ArrayCollection();
        $this->created = new DateTime();
    }

    public function getLastTransaction(): WalletImpTransaction
    {
        return $this->getTransactions()->last();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTotalAmount(): int
    {
        return $this->totalAmount;
    }

    public function setTotalAmount(int $totalAmount): WalletImp
    {
        $this->totalAmount = $totalAmount;
        return $this;
    }

    public function getActualPaid(): int
    {
        return $this->actualPaid;
    }

    public function setActualPaid(int $actualPaid): WalletImp
    {
        $this->actualPaid = $actualPaid;
        return $this;
    }

    public function getAvailableAmount(): int
    {
        $amount = $this->getTotalAmount() - $this->getFreezeAmount();
        return $amount > 0 ? $amount : 0;
    }

    public function getFreezeAmount(): int
    {
        return $this->freezeAmount;
    }

    public function setFreezeAmount(int $freezeAmount): WalletImp
    {
        $this->freezeAmount = $freezeAmount;
        return $this;
    }

    public function addTransaction(WalletImpTransaction $transaction): WalletImp
    {
        $transaction->setWallet($this);
        $this->transactions[] = $transaction;

        return $this;
    }

    public function removeTransaction(WalletImpTransaction $transaction): void
    {
        $this->transactions->removeElement($transaction);
    }

    /**
     * @return Collection
     */
    public function getTransactions(): Collection
    {
        return $this->transactions;
    }

    /**
     * @param int $amount
     * @param int $type
     * @return boolean
     * @throws BalanceTypeNotExistException
     */
    public function isAvailableAmount(int $amount,int $type = self::AVAILABLE_BALANCE): bool
    {
        switch ($type) {
            case WalletImp::AVAILABLE_BALANCE:
                return $this->getAvailableAmount() >= $amount;
            case WalletImp::FREEZE_BALANCE:
                return $this->getFreezeAmount() >= $amount && $this->getTotalAmount() >= $amount;
        }

        throw new BalanceTypeNotExistException;
    }
}

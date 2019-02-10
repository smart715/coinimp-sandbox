<?php


namespace App\Entity;


use App\Entity\Payment\Status;
use App\Entity\Payment\Transaction;
use DateTime;

class Payment
{
    private $id = 0;
    private $profile;

    private $amount;
    private $fee;
    private $status = Status::PENDING;
    private $timestamp;
    private $isManual = false;
    private $walletAddress = null;
    private $crypto;
    private $paymentId = '';

    /** @var Transaction|null */
    private $transaction = null;

    public function __construct(
        int $amount, float $fee, DateTime $timestamp, Profile $profile,
        string $walletAddress, Crypto $crypto, string $paymentId = '')
    {
        $this->amount = $amount;
        $this->fee = $fee;
        $this->timestamp = $timestamp;
        $this->profile = $profile;
        $this->walletAddress = $walletAddress;
        $this->crypto = $crypto;
        $this->paymentId = $paymentId;
    }

    public function getEffectiveAmount() :int
    {
        if ($this->getStatus() === Status::ERROR)
            return 0;

        return $this->getAmount();
    }

    public function getPaidAmount() :int
    {
        return $this->amount - $this->fee;
    }

    public function getAmount() :int
    {
        return $this->amount;
    }

    public function getFee() :int
    {
        return $this->fee;
    }

    public function setFee(int $fee) :void
    {
        $this->fee = $fee;
    }

    public function getTimestamp() :DateTime
    {
        return $this->timestamp;
    }

    public function getStatus() :string
    {
        return $this->status;
    }

    public function setStatus(string $status) :void
    {
        $this->status = $status;
    }

    public function isManual() :bool
    {
        return $this->isManual;
    }

    public function setTransaction(Transaction $transaction) :void
    {
        $this->transaction = $transaction;
    }

    public function getHash() :string
    {
        return $this->getTransaction()->getHash();
    }

    public function getKey() :string
    {
        return $this->getTransaction()->getKey();
    }

    public function getWalletAddress() :string
    {
        return $this->walletAddress ?? '-';
    }

    public function setCrypto(Crypto $crypto) :void
    {
        $this->crypto = $crypto;
    }

    public function getCrypto() :Crypto
    {
        return $this->crypto;
    }

    public function getEmail() :string
    {
        return $this->profile->getEmail();
    }

    public function getId() :int
    {
        return $this->id;
    }

    private function getTransaction() :Transaction
    {
        return $this->transaction
            ?? new Transaction('-', '-');
    }

    public function getPaymentId(): string
    {
        return $this->paymentId ?? '';
    }
}
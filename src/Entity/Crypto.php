<?php

namespace App\Entity;

class Crypto
{
    private $id;
    private $symbol;
    private $minimalPayout;
    private $fee;
    private $walletLengths = [];
    private $explorerUrl = '';

    public function __construct(
        string $symbol,
        float $minimalPayout,
        float $fee,
        array $walletLengths,
        string $explorerUrl
    )
    {
        $this->symbol = $symbol;
        $this->minimalPayout = $minimalPayout;
        $this->fee = $fee;
        $this->walletLengths = $walletLengths;
        $this->explorerUrl = $explorerUrl;
    }

    public function setFee(float $fee): void
    {
        $this->fee = $fee;
    }

    public function setMinimalPayout(float $minimalPayout): void
    {
        $this->minimalPayout = $minimalPayout;
    }

    public function setWalletLengths(array $walletLengths) :void
    {
        $this->walletLengths = $walletLengths;
    }

    public function setExplorerUrl(string $explorerUrl) :void
    {
        $this->explorerUrl = $explorerUrl;
    }

    public function getId() :int
    {
        return $this->id;
    }

    public function getSymbol() :string
    {
        return $this->symbol;
    }

    public function getFee(): float
    {
        return $this->fee;
    }

    public function getMinimalPayout(): float
    {
        return $this->minimalPayout;
    }

    public function getWalletLengths() :array
    {
        return $this->walletLengths;
    }

    public function getExplorerUrl() :string
    {
        return $this->explorerUrl;
    }
}
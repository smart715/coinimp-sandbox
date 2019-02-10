<?php

namespace App\Currency;

interface CurrencyInfoInterface
{
    public function getSymbol(): string;

    public function getRealBalance(): string;

    public function getRate(): string;

    public function getImpPriceInUsd(): string;

    public function getPriceInImp(): string;

    public function setAmount(string $amount): void;
}

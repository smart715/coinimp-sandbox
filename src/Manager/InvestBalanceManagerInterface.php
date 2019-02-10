<?php

namespace App\Manager;

use App\Entity\Profile;
use App\InvestGateway\InvestBalance;

interface InvestBalanceManagerInterface
{
    public function getImpPriceInUsd(): string;
    public function getInvestCurrencySymbols(): array;
    public function getRealBalance(Profile $profile, string $symbol): string;
    public function getInvestBalance(Profile $profile): InvestBalance;
    public function getSpentCurrency(Profile $profile, string $currency): string;
    public function getSpentCurrencies(Profile $profile): array;
}

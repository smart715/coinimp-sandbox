<?php

namespace App\Currency;

use App\Communications\CurrencyConverterInterface;
use App\Entity\Profile;
use App\Manager\InvestBalanceManager;

class CurrencyInfo implements CurrencyInfoInterface
{
    /** @var Profile */
    private $profile;

    /** @var string */
    private $symbol;

    /** @var string */
    private $amount = '0';

    /** @var string */
    private $rate;

    /** @var string */
    private $impAmount;

    /** @var CurrencyConverterInterface */
    private $currencyConverter;

    /** @var InvestBalanceManager */
    private $investBalanceManager;

    public function __construct(
        Profile $profile,
        string $symbol,
        string $impAmount,
        InvestBalanceManager $investBalanceManager,
        CurrencyConverterInterface $currencyConverter
    ) {
        $this->profile = $profile;
        $this->symbol = $symbol;
        $this->impAmount = $impAmount;
        $this->investBalanceManager = $investBalanceManager;
        $this->currencyConverter = $currencyConverter;
        $this->setAmountByImp($impAmount);
        bcscale(8);
    }

    public function getSymbol(): string
    {
        return $this->symbol;
    }

    public function getAmount(): string
    {
        return $this->amount;
    }

    public function getRealBalance(): string
    {
        return $this->investBalanceManager->getRealBalance($this->profile, $this->getSymbol());
    }

    public function getRate(): string
    {
        if (!$this->rate) {
            $this->rate = number_format($this->currencyConverter->getRate(
                $this->getSymbol(),
                CurrencyConverterInterface::USD
            ), 10, '.', '');
        }
        return $this->rate;
    }

    public function symbolIsValid(): bool
    {
        return in_array($this->getSymbol(), $this->investBalanceManager->getInvestCurrencySymbols());
    }

    public function getImpPriceInUsd(): string
    {
        return $this->investBalanceManager->getImpPriceInUsd();
    }

    public function getPriceInImp(): string
    {
        return $this->impAmount;
    }

    public function setAmount(string $amount): void
    {
        $this->amount = $amount;
        $this->setImpAmount($amount);
    }

    private function setImpAmount(string $amount): void
    {
        $this->impAmount = bcdiv(bcmul($this->getRate(), $amount), $this->getImpPriceInUsd());
    }

    private function setAmountByImp(string $ImpAmount): void
    {
        if (bccomp($this->getRate(), '0') > 0)
            $this->amount = bcdiv(bcmul($this->getImpPriceInUsd(), $ImpAmount), $this->getRate());
    }
}

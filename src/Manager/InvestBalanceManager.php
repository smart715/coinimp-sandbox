<?php

namespace App\Manager;

use App\Entity\Profile;
use App\InvestGateway\InvestBalance;
use App\InvestGateway\InvestGatewayCommunicator;

class InvestBalanceManager implements InvestBalanceManagerInterface
{
    /** @var InvestGatewayCommunicator */
    private $investCommunicator;

    /** @var WalletImpTransactionManager */
    private $walletImpTransactionManager;

    /** @var InvestBalance */
    private $balance;

    /** @var string[] */
    private $spentCurrencies;

    /** @var string */
    private $impPriceInUsd;

    public function __construct(
        InvestGatewayCommunicator $investCommunicator,
        WalletImpTransactionManager $walletImpTransactionManager,
        string $impPriceInUsd = '0'
    ) {
        $this->investCommunicator = $investCommunicator;
        $this->walletImpTransactionManager = $walletImpTransactionManager;
        $this->impPriceInUsd = $impPriceInUsd;
        bcscale(8);
    }

    public function getImpPriceInUsd(): string
    {
        return $this->impPriceInUsd;
    }

    public function getInvestCurrencySymbols(): array
    {
        return $this->investCommunicator->getInvestCurrencySymbols();
    }

    public function getRealBalance(Profile $profile, string $symbol): string
    {
        $investBalance = $this->getInvestBalance($profile)->getBalance($symbol);
        $spentAmount = $this->getSpentCurrency($profile, $symbol);
        $realBalance = bcsub($investBalance, $spentAmount);

        return bccomp($realBalance, '0') < 0 ? 0 : $realBalance;
    }

    public function getAllRealBalances(Profile $profile): array
    {
        return array_column(
            array_map(function (string $symbol) use ($profile) {
                return [
                    'symbol' => $symbol,
                    'balance' => $this->getRealBalance($profile, $symbol),
                ];
            }, $this->getInvestCurrencySymbols()),
            'balance',
            'symbol'
        );
    }

    public function getInvestBalance(Profile $profile): InvestBalance
    {
        if (!isset($this->balance)) {
            $this->balance = $this->investCommunicator->getBalance($profile->getUser()->getId());
        }

        return $this->balance;
    }

    public function getSpentCurrency(Profile $profile, string $currency): string
    {
        return $this->getSpentCurrencies($profile)[$currency] ?? '0';
    }

    public function getSpentCurrencies(Profile $profile): array
    {
        if (!$this->spentCurrencies)
            $this->spentCurrencies = $this->spentCurrencies($profile);

        return $this->spentCurrencies;
    }

    private function spentCurrencies(Profile $profile): array
    {
        $profileTransactions = $this->walletImpTransactionManager->getProfileTransactions($profile);

        $spentCurrencies = array_fill_keys($this->getInvestCurrencySymbols(), 0);

        foreach ($profileTransactions as $transaction) {
            $data = $transaction->getData();
            $currency = $data['currencySymbol'] ?? null;
            if ($currency && in_array($currency, $this->getInvestCurrencySymbols()))
                $spentCurrencies[$currency] = bcadd($spentCurrencies[$currency], $data['currencyAmount']);
        }

        return $spentCurrencies;
    }
}

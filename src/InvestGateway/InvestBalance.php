<?php

namespace App\InvestGateway;

use Exception;

class InvestBalance
{
    /** @var string[] */
    private $balances;

    /** @var string[] */
    private $investCurrencySymbols;

    public function __construct(array $balances, array $investCurrencySymbols)
    {
        $this->balances = $balances;
        $this->investCurrencySymbols = $investCurrencySymbols;
    }

    public function getBalance(string $symbol): string
    {
        return $this->balances[$symbol] ?? '0';
    }

    public function getAllBalances(): array
    {
        return array_column(
            array_map(function (string $symbol) {
                return [
                    'symbol' => $symbol,
                    'balance' => $this->getBalance($symbol),
                ];
            }, $this->getInvestCurrencySymbols()),
            'balance',
            'symbol'
        );
    }

    public function getInvestCurrencySymbols(): array
    {
        return $this->investCurrencySymbols;
    }
}

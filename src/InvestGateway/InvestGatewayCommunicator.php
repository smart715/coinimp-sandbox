<?php

namespace App\InvestGateway;

use App\Communications\JsonRpc;
use App\Entity\WalletImp\WalletImp;

class InvestGatewayCommunicator implements InvestGatewayCommunicatorInterface
{
    private const INVEST_CURRENCY_COEFFICIENT = [
        'XMR' => '0.000000000001',
        'BTC' => '1',
        'ETH' => '0.000000000000000001',
    ];

    /** @var JsonRpc */
    private $jsonRpc;

    /** @var string[] */
    private $investCurrencySymbols;

    /** @var bool */
    private $errFailedToConnect = false;

    public function __construct(
        JsonRpc $jsonRpc,
        array $investCurrencySymbols
    ) {
        $this->jsonRpc = $jsonRpc;
        $this->investCurrencySymbols = $investCurrencySymbols;
        bcscale(WalletImp::impUnitBase);
    }

    public function getInvestCurrencySymbols(): array
    {
        return $this->investCurrencySymbols;
    }

    public function getBalance(int $userId): InvestBalance
    {
        $apiResponse = $this->sendRequest('get_balance', [
            'user_id' => $userId,
        ]);

        $balances = $this->parseBalances($apiResponse);
        return new InvestBalance($balances, $this->getInvestCurrencySymbols());
    }

    public function getPayoutCredentials(int $userId, string $currency): array
    {
        return $this->sendRequest('get_payout_credentials', [
            'user_id' => $userId,
            'currency' => $currency,
        ]);
    }

    public function apiConnectionError(): bool
    {
        return $this->errFailedToConnect;
    }

    private function sendRequest(string $method, array $params): array
    {
        try {
            return $this->jsonRpc->send($method, $params);
        } catch (\Throwable $exception) {
            $this->errFailedToConnect = true;
            return [];
        }
    }

    private function parseBalances(array $balances): array
    {
        $parsedBalances = [];
        foreach ($balances as $currency => $balance) {
            $result = bcmul($balance, InvestGatewayCommunicator::INVEST_CURRENCY_COEFFICIENT[$currency]);
            $parsedBalances[$currency] = $result;
        }
        return $parsedBalances;
    }
}

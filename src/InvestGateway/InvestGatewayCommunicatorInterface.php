<?php

namespace App\InvestGateway;

interface InvestGatewayCommunicatorInterface
{
    public function getBalance(int $userId): InvestBalance;
    public function getPayoutCredentials(int $userId, string $currency): array;
}

<?php

namespace App\Tests\InvestGateway;

use App\Communications\JsonRpc;
use App\Entity\Profile;
use App\InvestGateway\InvestBalance;
use App\InvestGateway\InvestGatewayCommunicator;
use PHPUnit\Framework\TestCase;

class InvestGatewayCommunicatorTest extends TestCase
{
    public function testGetBalance(): void
    {
        $userId = 1;
        $balances = [
            'XMR' => '2000000000000',
            'BTC' => '1000000000',
            'ETH' => '15000000000000000000',
        ];

        $jsonRpc = $this->createMock(JsonRpc::class);
        $jsonRpc->method('send')
            ->with($this->equalTo('get_balance'), $this->equalTo(['user_id' => $userId]))
            ->willReturn($balances);

        $returnedBalances = [
            'XMR' => 2,
            'BTC' => 1000000000,
            'ETH' => 15,
        ];
        $investBalance = new InvestBalance($returnedBalances, array_keys($returnedBalances));
        $invest = new InvestGatewayCommunicator($jsonRpc, array_keys($returnedBalances));
        $this->assertEquals($investBalance, $invest->getBalance($userId));
    }

    public function testGetPayoutCredentials(): void
    {
        $userId = 1;

        $ethExpectedResponse = [
            'address' => '0xbd33c12b8ad3a269cf9a58ad6201fee85cc01050',
        ];

        $jsonRpc = $this->createMock(JsonRpc::class);

        $jsonRpc
            ->method('send')
            ->with(
                $this->equalTo('get_payout_credentials'),
                $this->equalTo(['user_id' => $userId, 'currency' => 'ETH'])
            )
            ->willReturn($ethExpectedResponse)
        ;

        $invest = new InvestGatewayCommunicator($jsonRpc, ['XMR', 'BTC', 'ETH']);

        $this->assertEquals($ethExpectedResponse, $invest->getPayoutCredentials($userId, 'ETH'));
    }
}

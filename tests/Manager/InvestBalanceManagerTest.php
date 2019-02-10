<?php

namespace App\Tests\Manager;

use App\Entity\Profile;
use App\Entity\User;
use App\Entity\WalletImp\WalletImpTransaction;
use App\InvestGateway\InvestBalance;
use App\InvestGateway\InvestGatewayCommunicator;
use App\Manager\InvestBalanceManager;
use App\Manager\WalletImpTransactionManager;
use PHPUnit\Framework\TestCase;

class InvestBalanceManagerTest extends TestCase
{
    private const XMR = 'XMR';
    private const BTC = 'BTC';
    private const ETH = 'ETH';
    private const CURRENCIES = [
        self::XMR,
        self::BTC,
        self::ETH,
    ];
    private const XMR_INVEST_AMOUNT = 789;
    private const BTC_INVEST_AMOUNT = 123;
    private const ETH_INVEST_AMOUNT = 456;
    private const XMR_SPENT_AMOUNT = 700;
    private const BTC_SPENT_AMOUNT = 100;
    private const ETH_SPENT_AMOUNT = 400;

    public function testGetRealBalance(): void
    {
        $investBalanceManager = $this->getInvestBalanceManager(.01, self::XMR, self::XMR_INVEST_AMOUNT);
        $xmrRealBalance = $investBalanceManager->getRealBalance($this->mockProfile(), self::XMR);
        $this->assertEquals(
            self::XMR_INVEST_AMOUNT - self::XMR_SPENT_AMOUNT,
            $xmrRealBalance
        );

        $investBalanceManager = $this->getInvestBalanceManager(.01, self::BTC, self::BTC_INVEST_AMOUNT);
        $btcRealBalance = $investBalanceManager->getRealBalance($this->mockProfile(), self::BTC);
        $this->assertEquals(
            self::BTC_INVEST_AMOUNT - self::BTC_SPENT_AMOUNT,
            $btcRealBalance
        );

        $investBalanceManager = $this->getInvestBalanceManager(.01, self::ETH, self::ETH_INVEST_AMOUNT);
        $ethRealBalance = $investBalanceManager->getRealBalance($this->mockProfile(), self::ETH);
        $this->assertEquals(
            self::ETH_INVEST_AMOUNT - self::ETH_SPENT_AMOUNT,
            $ethRealBalance
        );
    }

    private function getInvestBalanceManager(
        float $impPriceInUsd,
        string $currencySymbol,
        float $currencyInvestAmount
    ): InvestBalanceManager {
        return new InvestBalanceManager(
            $this->mockInvestGatewayCommunicator($currencySymbol, $currencyInvestAmount),
            $this->mockWalletImpTransactionManager(),
            $impPriceInUsd
        );
    }

    private function mockWalletImpTransactionManager(): WalletImpTransactionManager
    {
        $walletImpTransactionManagerMock = $this->createMock(WalletImpTransactionManager::class);
        $walletImpTransactionManagerMock
            ->expects($this->once())
            ->method('getProfileTransactions')
            ->willReturn($this->walletImpTransactionProvider())
        ;
        return $walletImpTransactionManagerMock;
    }

    private function mockWalletImpTransaction(array $data): WalletImpTransaction
    {
        $walletImpTransactionMock = $this->createMock(WalletImpTransaction::class);
        $walletImpTransactionMock
            ->expects($this->once())
            ->method('getData')
            ->willReturn($data)
        ;
        return $walletImpTransactionMock;
    }

    private function walletImpTransactionProvider(): array
    {
        return [
            $this->mockWalletImpTransaction([
                'currencySymbol' => self::XMR,
                'currencyAmount' => self::XMR_SPENT_AMOUNT,
            ]),
            $this->mockWalletImpTransaction([
                'currencySymbol' => self::BTC,
                'currencyAmount' => self::BTC_SPENT_AMOUNT,
            ]),
            $this->mockWalletImpTransaction([
                'currencySymbol' => self::ETH,
                'currencyAmount' => self::ETH_SPENT_AMOUNT,
            ]),
        ];
    }

    private function mockInvestGatewayCommunicator(
        string $currencySymbol,
        float $currencyInvestAmount
    ): InvestGatewayCommunicator {

        $investGatewayCommunicatorMock = $this->createMock(InvestGatewayCommunicator::class);
        $investGatewayCommunicatorMock
            ->expects($this->once())
            ->method('getBalance')
            ->willReturn($this->mockInvestBalance($currencySymbol, $currencyInvestAmount))
        ;
        $investGatewayCommunicatorMock
            ->method('getInvestCurrencySymbols')
            ->willReturn(self::CURRENCIES)
        ;
        return $investGatewayCommunicatorMock;
    }

    private function mockInvestBalance(string $currencySymbol, float $currencyInvestAmount): InvestBalance
    {
        $investBalanceMock = $this->createMock(InvestBalance::class);
        $investBalanceMock
            ->method('getBalance')
            ->with($currencySymbol)
            ->willReturn($currencyInvestAmount)
        ;
        return $investBalanceMock;
    }

    private function mockProfile(): Profile
    {
        $profileMock = $this->createMock(Profile::class);
        $profileMock
            ->expects($this->once())
            ->method('getUser')
            ->willReturn($this->mockUser())
        ;
        return $profileMock;
    }

    private function mockUser(): User
    {
        $userMock = $this->createMock(User::class);
        $userMock
            ->expects($this->once())
            ->method('getId')
            ->willReturn(1)
        ;
        return $userMock;
    }
}

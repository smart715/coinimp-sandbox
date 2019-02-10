<?php

namespace App\Tests\Manager;

use App\Communications\ApiCurrencyConverter;
use App\Communications\CurrencyConverterInterface;
use App\Entity\Profile;
use App\Manager\DepositImpManager;
use App\Manager\InvestBalanceManager;
use App\Manager\ProfileManager;
use App\WalletServices\SystemWalletConfig;
use App\WalletServices\WalletImpTransactionHandler;
use Exception;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpFoundation\JsonResponse;

class DepositImpManagerTest extends TestCase
{
    private const XMR = 'XMR';
    private const BTC = 'BTC';
    private const ETH = 'ETH';

    private const CURRENCY_SYMBOLS = [
        self::XMR,
        self::BTC,
        self::ETH,
    ];

    private const DEPOSIT_MIN_IMP_AMOUNT = 1;
    private const DEPOSIT_MAX_IMP_AMOUNT = 10000000;

    private const IMP_PRICE_IN_USD = .01;

    public function testDepositImpWithInvalidCurrency(): void
    {
        $depositImpManager = $this->getDepositImpManager([], 'BTC', 852, 147, .05);
        $depositResponse = $depositImpManager->depositImp($this->mockProfile(), 'ABC', 10);

        $this->assertEquals(
            'INVALID_CURRENCY',
            $depositResponse->getCode()
        );
    }

    public function testDepositImpWithZeroAmount(): void
    {
        $depositImpManager = $this->getDepositImpManager([], 'BTC', 852, 147, .05);
        $depositResponse = $depositImpManager->depositImp($this->mockProfile(), 'BTC', 0);

        $this->assertEquals(
            'NO_AMOUNT',
            $depositResponse->getCode()
        );
    }

    public function testDepositImpWithNotEnoughBalance(): void
    {
        $depositImpManager = $this->getDepositImpManager([], 'XMR', 0, 147, .05);
        $depositResponse = $depositImpManager->depositImp($this->mockProfile(), 'XMR', 988000000);

        $this->assertEquals(
            'NO_BALANCE',
            $depositResponse->getCode()
        );
    }

    public function testDepositImpWithGetRateFailure(): void
    {
        $depositImpManager = $this->getDepositImpManager([], 'ETH', 852, 0, .05);
        $depositResponse = $depositImpManager->depositImp($this->mockProfile(), 'ETH', 12);

        $this->assertEquals(
            'NO_AMOUNT',
            $depositResponse->getCode()
        );
    }

    public function testDepositImpWithoutCommission(): void
    {
        $depositImpManager = $this->getDepositImpManager([], 'BTC', 85, 147, 0, 1);
        $depositResponse = $depositImpManager->depositImp($this->mockProfile(true), 'BTC', 84);

        $this->assertEquals(
            'SUCCESS',
            $depositResponse->getCode()
        );
    }

    public function testDepositImpWithoutProfileReferencer(): void
    {
        $depositImpManager = $this->getDepositImpManager([], 'BTC', 85, 147, .05, 1);
        $depositResponse = $depositImpManager->depositImp($this->mockProfile(), 'BTC', 84);

        $this->assertEquals(
            'SUCCESS',
            $depositResponse->getCode()
        );
    }

    public function testDepositImpWithProfileReferencerAndCommission(): void
    {
        $depositImpManager = $this->getDepositImpManager([], 'BTC', 85, 147, .05, 1);
        $depositResponse = $depositImpManager->depositImp($this->mockProfile(true), 'BTC', 84);

        $this->assertEquals(
            'SUCCESS',
            $depositResponse->getCode()
        );
    }

    public function testDepositImpWithAmountLessThanMinAlowed(): void
    {
        $depositImpManager = $this->getDepositImpManager([], 'XMR', 123, 1, 0);
        $depositResponse = $depositImpManager->depositImp(
            $this->mockProfile(true),
            'XMR',
            .9 * self::IMP_PRICE_IN_USD
        );

        $this->assertEquals(
            'LOW_AMOUNT',
            $depositResponse->getCode()
        );
    }

    public function testDepositImpWithAmountMoreThanMaxAlowed(): void
    {
        $depositImpManager = $this->getDepositImpManager([], 'ETH', self::DEPOSIT_MAX_IMP_AMOUNT + 1, 1, 0);
        $depositResponse = $depositImpManager->depositImp(
            $this->mockProfile(true),
            'ETH',
            self::DEPOSIT_MAX_IMP_AMOUNT + 0.0001
        );

        $this->assertEquals(
            'HIGH_AMOUNT',
            $depositResponse->getCode()
        );
    }

    public function testDepositImpBonusPackage(): void
    {
        $depositImpManagerWithoutPackage = $this->getDepositImpManager([], 'BTC', 85, 147, .05, 1);
        $depositResponse = $depositImpManagerWithoutPackage->depositImp($this->mockProfile(true), 'BTC', 85);

        $this->assertEquals(
            85,
            $depositResponse->getCurrentImpAmount()
        );

        $depositImpManagerWithPackage = $this
            ->getDepositImpManager([['amount' => 10000, 'bonusValue' => 500]], 'BTC', 852, 147, .05, 1);
        $depositResponse = $depositImpManagerWithPackage->depositImp($this->mockProfile(true), 'BTC', 10000);
        $impAmount = 10000;

        $this->assertEquals(
            $impAmount + 500,
            $depositResponse->getCurrentImpAmount()
        );
    }

    public function testDepositImpWithNotEnoughImp(): void
    {
        $depositImpManager = new DepositImpManager(
            [],
            $this->mockApiCurrencyConverter(147),
            $this->mockWalletImpTransactionHandlerWithError(),
            $this->mockInvestBalanceManager('BTC', 85),
            $this->mockProfileManager(),
            $this->mockSystemWalletConfig(0)
        );
        $depositResponse = $depositImpManager->depositImp($this->mockProfile(true), 'BTC', 85);

        $this->assertEquals(
            'NO_IMP',
            $depositResponse->getCode()
        );
    }

    private function getDepositImpManager(
        array $impBonusPackages,
        string $currencySymbol,
        float $currencyRealBalance,
        float $currencyRate,
        float $commissionPercentage,
        int $howMuchCallMoveAmountToUser = 0
    ): DepositImpManager {
        return new DepositImpManager(
            $impBonusPackages,
            $this->mockApiCurrencyConverter($currencyRate),
            $this->mockWalletImpTransactionHandler($howMuchCallMoveAmountToUser),
            $this->mockInvestBalanceManager($currencySymbol, $currencyRealBalance),
            $this->mockProfileManager(),
            $this->mockSystemWalletConfig($commissionPercentage)
        );
    }

    private function mockApiCurrencyConverter(float $rate): ApiCurrencyConverter
    {
        $apiCurrencyConverterMock = $this->createMock(ApiCurrencyConverter::class);

        $apiCurrencyConverterMock
            ->method('getRate')
            ->willReturn($rate)
        ;
        return $apiCurrencyConverterMock;
    }

    private function mockWalletImpTransactionHandler(int $howMuchCallMoveAmountToUser): WalletImpTransactionHandler
    {
        $transactionHandlerMock = $this->createMock(WalletImpTransactionHandler::class);

        if ($howMuchCallMoveAmountToUser) {
            $transactionHandlerMock
                ->expects($this->exactly($howMuchCallMoveAmountToUser))
                ->method('moveAmountToUser');
            return $transactionHandlerMock;
        }

        $transactionHandlerMock
            ->method('moveAmountToUser')
        ;

        return $transactionHandlerMock;
    }

    private function mockWalletImpTransactionHandlerWithError(): WalletImpTransactionHandler
    {
        $transactionHandlerMock = $this->createMock(WalletImpTransactionHandler::class);

        $transactionHandlerMock
            ->method('moveAmountToUser')
            ->will($this->throwException(new Exception()));
        ;

        return $transactionHandlerMock;
    }

    private function mockInvestBalanceManager(
        string $currencySymbol,
        float $currencyRealBalance
    ): InvestBalanceManager {

        $investBalanceManagerMock = $this->createMock(InvestBalanceManager::class);

        $investBalanceManagerMock
            ->method('getInvestCurrencySymbols')
            ->willReturn(self::CURRENCY_SYMBOLS)
        ;
        $investBalanceManagerMock
            ->method('getRealBalance')
            ->with($this->mockProfile(), $currencySymbol)
            ->willReturn($currencyRealBalance)
        ;
        $investBalanceManagerMock
            ->method('getImpPriceInUsd')
            ->willReturn(self::IMP_PRICE_IN_USD)
        ;
        return $investBalanceManagerMock;
    }

    private function mockProfileManager(): ProfileManager
    {
        $profileManagerMock = $this->createMock(ProfileManager::class);

        $profileManagerMock
            ->method('findByEmail')
            ->willReturn($this->mockProfile())
        ;
        return $profileManagerMock;
    }

    private function mockSystemWalletConfig(float $commissionPercentage): SystemWalletConfig
    {
        $systemWalletConfigMock = $this->createMock(SystemWalletConfig::class);

        $systemWalletConfigMock
            ->method('getCurrentWalletProfile')
            ->willReturn('profile_name')
        ;
        $systemWalletConfigMock
            ->method('getCommissionPercentage')
            ->willReturn($commissionPercentage)
        ;
        $systemWalletConfigMock
            ->method('getDepositImpMinAmount')
            ->willReturn(self::DEPOSIT_MIN_IMP_AMOUNT)
        ;
        $systemWalletConfigMock
            ->method('calculateAvailableAmount')
            ->willReturn(self::DEPOSIT_MAX_IMP_AMOUNT)
        ;
        return $systemWalletConfigMock;
    }

    private function mockProfile(bool $hasIcoRefrencer = false): Profile
    {
        $profileMock = $this->createMock(Profile::class);

        if ($hasIcoRefrencer) {
            $profileMock
                ->method('getIcoReferencer')
                ->willReturn($this->mockProfile());

            return $profileMock;
        }

        return $profileMock;
    }
}

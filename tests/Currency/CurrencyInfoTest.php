<?php

namespace App\Tests\Currency;

use App\Communications\ApiCurrencyConverter;
use App\Currency\CurrencyInfo;
use App\Entity\Profile;
use App\Manager\InvestBalanceManager;
use PHPUnit\Framework\TestCase;

class CurrencyInfoTest extends TestCase
{
    private const XMR = 'XMR';
    private const BTC = 'BTC';
    private const ETH = 'ETH';

    private const CURRENCY_SYMBOLS = [
        self::XMR,
        self::BTC,
        self::ETH,
    ];

    public function testIsValidSymbol(): void
    {
        $currencyInfo = $this->getCurrencyInfo(
            $this->mockProfile(),
            'ABC',
            789,
            $this->mockInvestBalanceManager(self::CURRENCY_SYMBOLS, 123, .01),
            $this->mockApiCurrencyConverter(456)
        );

        $this->assertEquals(false, $currencyInfo->symbolIsValid());
    }

    public function testGetPriceInImp(): void
    {
        $currencyInfo = $this->getCurrencyInfo(
            $this->mockProfile(),
            'BTC',
            11598300,
            $this->mockInvestBalanceManager(self::CURRENCY_SYMBOLS, 987, .01),
            $this->mockApiCurrencyConverter(147)
        );

        $this->assertEquals(789, $currencyInfo->getAmount());
    }

    private function getCurrencyInfo(
        Profile $profile,
        string $symbol,
        float $impAmount,
        InvestBalanceManager $investBalanceManager,
        ApiCurrencyConverter $currencyConverter
    ): CurrencyInfo {

        return new CurrencyInfo(
            $profile,
            $symbol,
            $impAmount,
            $investBalanceManager,
            $currencyConverter
        );
    }

    private function mockProfile(): Profile
    {
        return $this->createMock(Profile::class);
    }

    private function mockApiCurrencyConverter(float $rate): ApiCurrencyConverter
    {
        $currencyConverterMock = $this->createMock(ApiCurrencyConverter::class);

        $currencyConverterMock
            ->method('getRate')
            ->willReturn($rate)
        ;
        return $currencyConverterMock;
    }

    private function mockInvestBalanceManager(
        array $investCurrencySymbols,
        float $realBalance,
        float $impPriceInUsd
    ): InvestBalanceManager {

        $investBalanceManagerMock = $this->createMock(InvestBalanceManager::class);

        $investBalanceManagerMock
            ->method('getInvestCurrencySymbols')
            ->willReturn($investCurrencySymbols)
        ;
        $investBalanceManagerMock
            ->method('getRealBalance')
            ->willReturn($realBalance)
        ;
        $investBalanceManagerMock
            ->method('getImpPriceInUsd')
            ->willReturn($impPriceInUsd)
        ;
        return $investBalanceManagerMock;
    }
}

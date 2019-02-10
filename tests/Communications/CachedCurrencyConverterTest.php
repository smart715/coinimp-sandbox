<?php

namespace App\Tests\Communications;

use App\Communications\CachedCurrencyConverter;
use App\Communications\CurrencyConverterInterface;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Cache\CacheItemInterface;
use Psr\Cache\CacheItemPoolInterface;

class CachedCurrencyConverterTest extends TestCase
{
    public function testGetRateWhenCacheIsHit(): void
    {
        $apiCurrencyConverter = $this->mockApiCurrencyConverter(100.0);
        $cacheItemPool = $this->mockCacheItemPoolInterface(2, true);
        $currencyConverter = new CachedCurrencyConverter($cacheItemPool, $apiCurrencyConverter);
        $this->assertEquals(2.0, $currencyConverter->getRate('x', 'y'));
    }

    public function testGetRateWhenCacheIsNotHit(): void
    {
        $apiCurrencyConverter = $this->mockApiCurrencyConverter(100.0);
        $cacheItemPool = $this->mockCacheItemPoolInterface(2, false);
        $currencyConverter = new CachedCurrencyConverter($cacheItemPool, $apiCurrencyConverter);
        $this->assertEquals(100.0, $currencyConverter->getRate('x', 'y'));
    }

    /**
     * @param mixed $value
     * @param bool $isHit
     * @return CacheItemPoolInterface
     */
    private function mockCacheItemPoolInterface($value, bool $isHit): CacheItemPoolInterface
    {
        /** @var CacheItemPoolInterface|MockObject $cache */
        $cache = $this->createMock(CacheItemPoolInterface::class);
        $cacheItem = $this->mockCacheItemInterface($value, $isHit);
        $cache->method('getItem')->willReturn($cacheItem);
        return $cache;
    }

    private function mockCacheItemInterface(string $get, bool $isHit): CacheItemInterface
    {
        /** @var CacheItemInterface|MockObject $cacheItem */
        $cacheItem = $this->createMock(CacheItemInterface::class);
        $cacheItem->method('get')->willReturn($get);
        $cacheItem->method('isHit')->willReturn($isHit);
        return $cacheItem;
    }

    private function mockApiCurrencyConverter(float $rate): CurrencyConverterInterface
    {
        /** @var CurrencyConverterInterface|MockObject $currencyConverter */
        $currencyConverter = $this->createMock(CurrencyConverterInterface::class);
        $currencyConverter->method('getRate')->willReturn($rate);
        return $currencyConverter;
    }
}

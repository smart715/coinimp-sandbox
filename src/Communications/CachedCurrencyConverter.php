<?php

namespace App\Communications;

use Psr\Cache\CacheItemPoolInterface;

class CachedCurrencyConverter implements CurrencyConverterInterface
{
    /** @var CacheItemPoolInterface */
    private $appCache;

    /** @var CurrencyConverterInterface */
    private $currencyConverter;

    private const USD_RATE = 'usdRate';

    public function __construct(
        CacheItemPoolInterface $appCache,
        CurrencyConverterInterface $currencyConverter
    ) {
        $this->appCache = $appCache;
        $this->currencyConverter = $currencyConverter;
    }

    public function getRate(string $from, string $to): float
    {
        if (!$this->appCache->getItem(self::USD_RATE.$from)->isHit())
            return $this->getCurrentRate($from, $to);
        return $this->getCachedRate(self::USD_RATE.$from);
    }

    private function getCurrentRate(string $from, string $to): float
    {
        $rate = $this->currencyConverter->getRate($from, $to);

        $cacheItem = $this->appCache->getItem(self::USD_RATE.$from);
        $cacheItem->set($rate);
        $cacheItem->expiresAfter(900);

        $this->appCache->save($cacheItem);

        return $rate;
    }

    private function getCachedRate(string $key): float
    {
        return (float)$this->appCache->getItem($key)->get() ?? 0.0;
    }
}

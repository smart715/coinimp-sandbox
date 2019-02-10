<?php

namespace App\DataSource\NetworkDataSource;

use App\Communications\CurrentTime;
use App\DataSource\NetworkCache\NetworkCache;
use App\Entity\Crypto;
use DateInterval;

class CachedNetworkDataSource implements NetworkDataSourceStrategy
{
    /** @var NetworkDataSourceStrategy */
    private $innerSource;

    /** @var NetworkCache */
    private $cache;

    /** @var CurrentTime */
    private $currentTime;

    /** @var DateInterval */
    private $expirationInterval;

    public function __construct(
        NetworkDataSourceStrategy $innerSource,
        NetworkCache $cache,
        CurrentTime $currentTime,
        DateInterval $expirationInterval
    ) {
        $this->innerSource = $innerSource;
        $this->cache = $cache;
        $this->currentTime = $currentTime;
        $this->expirationInterval = $expirationInterval;
    }

    public function getNetworkDifficulty(): int
    {
        $this->fillCache();
        return $this->cache->getNetworkDifficulty();
    }

    public function getLastBlockReward(): int
    {
        $this->fillCache();
        return $this->cache->getLastBlockReward();
    }

    public function isValidCrypto(Crypto $crypto): bool
    {
        return $this->innerSource->isValidCrypto($crypto);
    }

    private function fillCache(): void
    {
        if ($this->isCacheInvalid())
            $this->refreshCache();
    }

    private function refreshCache(): void
    {
        $this->cache->update(
            $this->innerSource->getNetworkDifficulty(),
            $this->innerSource->getLastBlockReward(),
            $this->currentTime->getCurrentTime()
        );
    }

    private function isCacheInvalid(): bool
    {
        return $this->cache->isEmpty() || $this->isCacheExpired();
    }

    private function isCacheExpired(): bool
    {
        $expirationTime = $this->cache->getTimestamp();
        $expirationTime->add($this->expirationInterval);

        return $this->currentTime->getCurrentTime() > $expirationTime;
    }
}

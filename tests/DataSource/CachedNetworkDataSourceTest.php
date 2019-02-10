<?php

namespace App\Tests\DataSource;

use App\Communications\CurrentTime;
use App\DataSource\NetworkCache\NetworkCache;
use App\DataSource\NetworkDataSource\CachedNetworkDataSource;
use App\DataSource\NetworkDataSource\NetworkDataSourceStrategy;
use DateInterval;
use DateTime;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class CachedNetworkDataSourceTest extends TestCase
{
    private const INNER_DIFFICULTY = 123;
    private const INNER_REWARD = 1000;
    private const CACHE_DIFFICULTY = 456;
    private const CACHE_REWARD = 2000;

    public function testSourceReturnsSameValueAsInnerDataSourceIfCacheEmpty(): void
    {
        $cache = new FakeNetworkCache(new DateTime(), 0, 0, true);

        $cachedSource = new CachedNetworkDataSource(
            $this->createInnerSource(),
            $cache,
            $this->createCurrentTime(new DateTime('2018-01-19 20:25:00')),
            new DateInterval('PT1M') // one minute
        );
        $this->assertEquals(self::INNER_DIFFICULTY, $cachedSource->getNetworkDifficulty());
        $this->assertEquals(self::INNER_REWARD, $cachedSource->getLastBlockReward());
    }

    public function testSourceReturnsValueFromCacheIfItNotExpired(): void
    {
        $currentTimestamp = new DateTime('2018-01-19 20:25:00');
        $cacheTimestamp = new DateTime('2018-01-19 20:24:00');
        $cacheExpiry = new DateInterval('PT1M'); // one minute

        $cachedSource = new CachedNetworkDataSource(
            $this->createInnerSource(),
            $this->createCache($cacheTimestamp),
            $this->createCurrentTime($currentTimestamp),
            $cacheExpiry
        );
        $this->assertEquals(self::CACHE_DIFFICULTY, $cachedSource->getNetworkDifficulty(
        ));
        $this->assertEquals(self::CACHE_REWARD, $cachedSource->getLastBlockReward());
    }

    public function testSourceReturnsSameValueAsInnerDataSourceIfCacheExpired(): void
    {
        $currentTimestamp = new DateTime('2018-01-19 20:25:00');
        $cacheTimestamp = new DateTime('2018-01-19 20:23:00'); // two minutes passed
        $cacheExpiry = new DateInterval('PT1M'); // one minute

        $cachedSource = new CachedNetworkDataSource(
            $this->createInnerSource(),
            $this->createCache($cacheTimestamp),
            $this->createCurrentTime($currentTimestamp),
            $cacheExpiry
        );
        $this->assertEquals(self::INNER_DIFFICULTY, $cachedSource->getNetworkDifficulty(
        ));
        $this->assertEquals(self::INNER_REWARD, $cachedSource->getLastBlockReward());
    }

    private function createInnerSource(): NetworkDataSourceStrategy
    {
        /** @var NetworkDataSourceStrategy|MockObject $innerSource */
        $innerSource = $this->createMock(NetworkDataSourceStrategy::class);
        $innerSource->method('getNetworkDifficulty')->willReturn(self::INNER_DIFFICULTY);
        $innerSource->method('getLastBlockReward')->willReturn(self::INNER_REWARD);
        return $innerSource;
    }

    private function createCache(DateTime $timestamp): NetworkCache
    {
        return new FakeNetworkCache(
            $timestamp,
            self::CACHE_DIFFICULTY,
            self::CACHE_REWARD,
            false
        );
    }

    private function createCurrentTime(DateTime $timestamp): CurrentTime
    {
        /** @var CurrentTime|MockObject $time */
        $time = $this->createMock(CurrentTime::class);
        $time->method('getCurrentTime')->willReturn($timestamp);
        return $time;
    }
}

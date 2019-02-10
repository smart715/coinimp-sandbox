<?php

namespace App\Tests\DataSource;

use App\DataSource\NetworkCache\NetworkCache;
use Datetime;

/**
 * Stub class used by CachedNetworkDataSourceTest
 */
class FakeNetworkCache implements NetworkCache
{
    /** @var Datetime */
    private $timestamp;

    /** @var int */
    private $difficulty;

    /** @var int */
    private $reward;

    /** @var bool */
    private $isEmpty;

    public function __construct(DateTime $timestamp, int $difficulty, int $reward, bool $isEmpty)
    {
        $this->update($difficulty, $reward, $timestamp);
        $this->isEmpty = $isEmpty;
    }

    public function getTimestamp(): DateTime
    {
        return $this->timestamp;
    }
    public function getNetworkDifficulty(): int
    {
        return $this->difficulty;
    }
    public function getLastBlockReward(): int
    {
        return $this->reward;
    }
    public function isEmpty(): bool
    {
        return $this->isEmpty;
    }

    public function update(
        int $networkDifficulty,
        int $lastBlockReward,
        DateTime $timestamp
    ): void {
        $this->difficulty = $networkDifficulty;
        $this->reward = $lastBlockReward;
        $this->timestamp = $timestamp;
        $this->isEmpty = false;
    }
}

<?php

namespace App\DataSource\NetworkCache;

use DateTime;

interface NetworkCache
{
    public function getNetworkDifficulty(): int;
    public function getLastBlockReward(): int;
    public function getTimestamp(): DateTime;
    public function isEmpty(): bool;
    public function update(
        int $networkDifficulty,
        int $lastBlockReward,
        DateTime $timestamp
    ): void;
}

<?php

namespace App\Manager\Model;

class TotalStats
{
    /** @var float */
    private $rate;

    /** @var int */
    private $hashes;

    /** @var int */
    private $pendingReward;

    /** @var int */
    private $totalReward;

    public function __construct(
        float $rate,
        int $hashes,
        int $pendingReward,
        int $totalReward
    ) {
        $this->rate = $rate;
        $this->hashes = $hashes;
        $this->pendingReward = $pendingReward;
        $this->totalReward = $totalReward;
    }

    public function getHashes(): int
    {
        return $this->hashes;
    }

    public function getRate(): float
    {
        return $this->rate;
    }

    public function getPendingReward(): int
    {
        return $this->pendingReward;
    }

    public function getTotalReward(): int
    {
        return $this->totalReward;
    }
}

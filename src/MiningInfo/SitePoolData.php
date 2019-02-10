<?php

namespace App\MiningInfo;

class SitePoolData
{
    /** @var float */
    private $hashRate;

    /** @var int */
    private $hashesTotal;

    public function __construct(float $hashRate, int $hashesTotal)
    {
        $this->hashRate = $hashRate;
        $this->hashesTotal = $hashesTotal;
    }

    public function getHashRate(): float
    {
        return $this->hashRate;
    }

    public function getHashesTotal(): int
    {
        return $this->hashesTotal;
    }
}

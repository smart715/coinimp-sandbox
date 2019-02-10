<?php

namespace App\Payer\Config;

class Config
{
    /** @var int */
    private $minimalPayout;

    /** @var int */
    private $fee;

    public function __construct(int $minimalPayout, int $fee)
    {
        $this->minimalPayout = $minimalPayout;
        $this->fee = $fee;
    }

    public function getMinimalPayout(): int
    {
        return $this->minimalPayout;
    }

    public function getFee(): int
    {
        return $this->fee;
    }
}

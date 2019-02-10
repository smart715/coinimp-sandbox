<?php

namespace App\MiningInfo\GlobalPoolData;

use App\MiningInfo\GlobalPoolDataInterface;

class GlobalPoolData implements GlobalPoolDataInterface
{
    /** @var int */
    private $difficulty;

    /** @var int */
    private $blockReward;

    /** @var float */
    private $payoutPercentage;

    /** @var float */
    private $payoutWithoutAdsPercentage;

    /** @var float */
    private $referralPercentage;

    /** @var float */
    private $orphanFeePercentage;

    public function __construct(
        int $difficulty,
        int $blockReward,
        float $payoutPercentage,
        float $payoutWithoutAdsPercentage,
        float $referralPercentage,
        float $orphanFeePercentage
    ) {
        $this->difficulty = $difficulty;
        $this->blockReward = $blockReward;
        $this->payoutPercentage = $payoutPercentage;
        $this->payoutWithoutAdsPercentage = $payoutWithoutAdsPercentage;
        $this->referralPercentage = $referralPercentage;
        $this->orphanFeePercentage = $orphanFeePercentage;
    }

    public function getDifficulty(): int
    {
        return $this->difficulty;
    }

    public function getBlockReward(): int
    {
        return $this->blockReward;
    }

    public function getPayoutPerMillion(): int
    {
        return (int)(pow(10, 6)
            * $this->getBlockReward()
            / $this->getDifficulty()
            * (1 - $this->getOrphanFeePercentage() / 100)
            * ($this->getPayoutPercentage() / 100));
    }

    public function getPayoutPercentage(): float
    {
        return $this->payoutPercentage;
    }

    public function getPayoutWithoutAdsPercentage(): float
    {
        return $this->payoutWithoutAdsPercentage;
    }

    public function getReferralPercentage(): float
    {
        return $this->referralPercentage;
    }

    public function getOrphanFeePercentage(): float
    {
        return $this->orphanFeePercentage;
    }

    public function calculateRewardFor(int $hashesCount): int
    {
        assert($hashesCount >= 0);

        return $this->calculateReward(
            $hashesCount,
            $this->getPayoutPercentage() / 100
        );
    }

    public function calculateReferralRewardFor(int $hashesCount): int
    {
        assert($hashesCount >= 0);

        return $this->calculateReward(
            $hashesCount,
            $this->getReferralPercentage() / 100
        );
    }

    private function calculateReward(int $hashesCount, float $factor): int
    {
        assert($hashesCount >= 0);
        assert($factor >= 0.0);

        return intval(round(
            $hashesCount
            * $this->getBlockReward()
            / $this->getDifficulty()
            * (1 - $this->getOrphanFeePercentage() / 100)
            * $factor,
            0
        ));
    }
}

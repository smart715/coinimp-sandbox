<?php

namespace App\MiningInfo;

interface GlobalPoolDataInterface
{
    public function getDifficulty(): int;
    public function getBlockReward(): int;
    public function getPayoutPerMillion(): int;
    public function getPayoutPercentage(): float;
    public function getPayoutWithoutAdsPercentage(): float;
    public function getReferralPercentage(): float;
    public function getOrphanFeePercentage(): float;
    public function calculateRewardFor(int $hashesCount): int;
    public function calculateReferralRewardFor(int $hashesCount): int;
}

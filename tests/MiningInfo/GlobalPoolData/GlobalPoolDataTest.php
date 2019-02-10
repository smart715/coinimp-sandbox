<?php

namespace App\Tests\MiningInfo\GlobalPoolData;

use App\MiningInfo\GlobalPoolData\GlobalPoolData;
use PHPUnit\Framework\TestCase;

class GlobalPoolDataTest extends TestCase
{
    private const DIFFICULTY = 56428954072;
    private const BLOCK_REWARD = 4633853975361;
    private const PAYOUT_PERCENTAGE = 99;
    private const PAYOUT_WITHOUT_ADS_PERCENTAGE = 95;
    private const ORPHAN_FEE_PERCENTAGE = 1;
    private const REFERRAL_PERCENTAGE = 1;

    public function testGetDifficulty(): void
    {
        $poolData = $this->getGlobalPoolData();
        $this->assertEquals(self::DIFFICULTY, $poolData->getDIFFICULTY());
    }

    public function testGetBlockReward(): void
    {
        $poolData = $this->getGlobalPoolData();
        $this->assertEquals(self::BLOCK_REWARD, $poolData->getBlockReward());
    }

    public function testGetPayoutPercentage(): void
    {
        $poolData = $this->getGlobalPoolData();
        $this->assertEquals(self::PAYOUT_PERCENTAGE, $poolData->getPayoutPercentage());
    }

    public function testGetRefferalPercentage(): void
    {
        $poolData = $this->getGlobalPoolData();
        $this->assertEquals(self::REFERRAL_PERCENTAGE, $poolData->getReferralPercentage());
    }

    public function testGetOrphanFeePercentage(): void
    {
        $poolData = $this->getGlobalPoolData();
        $this->assertEquals(self::ORPHAN_FEE_PERCENTAGE, $poolData->getOrphanFeePercentage());
    }

    public function testGetPayoutPerMillion(): void
    {
        $poolData = $this->getGlobalPoolData();
        $this->assertEquals(80484218, $poolData->getPayoutPerMillion());
    }

    public function testCalculateRewardFor(): void
    {
        $poolData = $this->getGlobalPoolData();
        $this->assertEquals(40242109, $poolData->calculateRewardFor(500000));
    }

    public function testCalculateReferralReward(): void
    {
        $poolData = $this->getGlobalPoolData();
        $this->assertEquals(406486, $poolData->calculateReferralRewardFor(500000));
    }

    private function getGlobalPoolData(): GlobalPoolData
    {
        return new GlobalPoolData(
            self::DIFFICULTY,
            self::BLOCK_REWARD,
            self::PAYOUT_PERCENTAGE,
            self::PAYOUT_WITHOUT_ADS_PERCENTAGE,
            self::REFERRAL_PERCENTAGE,
            self::ORPHAN_FEE_PERCENTAGE
        );
    }
}

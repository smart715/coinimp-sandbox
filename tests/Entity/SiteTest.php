<?php

namespace App\Tests\Entity;

use App\Entity\Crypto;
use App\Entity\Profile;
use App\Entity\Site;
use App\MiningInfo\GlobalPoolDataInterface;
use App\MiningInfo\SitePoolData;
use App\MiningInfo\SitePoolDataContainer;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class SiteTest extends TestCase
{
    public function testNewSiteIsClean() :void
    {
        $site = $this->createSite(false);
        $cryptoMock = $this->createCryptoMock('xmr');

        $this->assertEquals(0, $site->getReward());
        $this->assertEquals(0, $site->getHashesCount());
    }

    public function testUpdateWithPoolDataAddsComputedReward() :void
    {
        $site = $this->createSite(false);
        $cryptoMock = $this->createCryptoMock('xmr');

        $site->updateWithPoolData($this->createSiteData(1000), $this->createPoolData
        (2), 0.0);

        $this->assertEquals(2000, $site->getReward());
        $this->assertEquals(1000, $site->getHashesCount());
    }

    public function testUpdateWithPoolDataPreservesOldReward() :void
    {
        $site = $this->createSite(false);
        $cryptoMock = $this->createCryptoMock('xmr');

        $site->updateWithPoolData(
            $this->createSiteData(1000),
            $this->createPoolData(4),
            0.0
        );
        $site->updateWithPoolData(
            $this->createSiteData(2000), // difference is 1000 hashes
            $this->createPoolData(3), // less reward per hash
            0.0
        );
        $this->assertEquals(
            1000 * 4 + 1000 * 3,
            $site->getReward()
        );
        $this->assertEquals(2000, $site->getHashesCount());
    }

    public function testUpdateWithPoolDataDoesNotAddDoubleReward() :void
    {
        $site = $this->createSite(false);
        $cryptoMock = $this->createCryptoMock('xmr');

        $site->updateWithPoolData(
            $this->createSiteData(1000),
            $this->createPoolData(4),
            0.0
        );
        $site->updateWithPoolData(
            $this->createSiteData(1000), // difference is 0 hashes
            $this->createPoolData(3),
            0.0
        );
        $this->assertEquals(1000 * 4, $site->getReward());
        $this->assertEquals(1000, $site->getHashesCount());
    }

    /**
     * @dataProvider updateWithPoolDataReferralProvider
     * @param bool $isReferred
     * @param int $expectedReward
     */
    public function testUpdateWithPoolDataAddsReferralReward(
        bool $isReferred, int $expectedReward) :void
    {
        $site = $this->createSite($isReferred);
        $cryptoMock = $this->createCryptoMock('xmr');

        $site->updateWithPoolData(
            $this->createSiteData(1000),
            $this->createPoolData(2, 0.4),
            0.0
        );

        $this->assertEquals($expectedReward, $site->getReferralReward());
    }

    public function updateWithPoolDataReferralProvider() :array
    {
        return [
            [ true, 400 ],
            [ false, 0 ],
        ];
    }

    public function testUpdateWithPoolDataAddsFeeToTotalHashes() :void
    {
        $site = $this->createSite(false);
        $cryptoMock = $this->createCryptoMock('xmr');

        $site->updateWithPoolData(
            $this->createSiteData(1000),
            $this->createPoolData(),
            0.04
        );

        // 1000 - 4%
        $this->assertEquals(960, $site->getHashesCount());
    }

    public function testUpdateWithPoolDataAddsFeeToHashRate() :void
    {
        $site = $this->createSite(false);
        $cryptoMock = $this->createCryptoMock('xmr');

        $site->updateWithPoolData(
            $this->createSiteData(0, 10),
            $this->createPoolData(),
            0.04
        );

        // 10 - 4%
        $this->assertEquals(9.6, $site->getHashRate());
    }

    public function testUpdateWithPoolDataAddsFeeToReward() :void
    {
        $site = $this->createSite(false);
        $cryptoMock = $this->createCryptoMock('xmr');

        $site->updateWithPoolData(
            $this->createSiteData(1000),
            $this->createPoolData(2),
            0.04
        );

        // 2000 - 4%
        $this->assertEquals(1920, $site->getReward());
    }

    public function testUpdateWithPoolDataAddsFeeToReferralReward() :void
    {
        $site = $this->createSite(true);
        $cryptoMock = $this->createCryptoMock('xmr');

        $site->updateWithPoolData(
            $this->createSiteData(1000),
            $this->createPoolData(1, 0.25),
            0.04
        );

        // (1000 * 0.25) - 4%
        $this->assertEquals(240, $site->getReferralReward());
    }

    public function testUpdateWithPoolDataCorrectlyHandlesMultipleFees() :void
    {
        $site = $this->createSite(true);
        $cryptoMock = $this->createCryptoMock('xmr');

        $site->updateWithPoolData(
            $this->createSiteData(1000),
            $this->createPoolData(2, 0.5),
            0.04
        );
        $site->updateWithPoolData(
            $this->createSiteData(2000),
            $this->createPoolData(2, 0.5),
            0.02
        );
        $site->updateWithPoolData(
            $this->createSiteData(3000),
            $this->createPoolData(2, 0.5),
            0.04
        );

        $this->assertEquals(960 + 980 + 960, $site->getHashesCount());
        $this->assertEquals(1920 + 1960 + 1920, $site->getReward());
        $this->assertEquals(480 + 490 + 480, $site->getReferralReward());
    }

    private function createSite(bool $isReferred) :Site
    {
        $site = new Site;

        $reflection = new ReflectionClass($site);
        $profileField = $reflection->getProperty('profile');
        $profileField->setAccessible(true);
        $profileField->setValue($site, $this->mockProfile($isReferred));

        return $site;
    }

    private function createPoolData(
        float $rewardModifier = 1,
        float $referralModifier = 0.1) :GlobalPoolDataInterface
    {
        $poolData = $this->createMock(GlobalPoolDataInterface::class);
        $poolData->method('calculateRewardFor')->willReturnCallback(
            function(int $hashes) use ($rewardModifier)
            { return $hashes * $rewardModifier; }
        );
        $poolData->method('calculateReferralRewardFor')->willReturnCallback(
            function(int $hashes) use ($referralModifier)
            { return $hashes * $referralModifier; }
        );
        return $poolData;
    }

    private function createSiteData(int $hashesCount, float $hashRate = 0.0) :SitePoolDataContainer
    {
        $siteData = $this->createMock(SitePoolData::class);
        $siteData->method('getHashesTotal')->willReturn($hashesCount);
        $siteData->method('getHashRate')->willReturn($hashRate);

        $container = new SitePoolDataContainer([$siteData]);

        return $container;
    }

    /**
     * @param bool $isReferred
     * @return Profile
     */
    private function mockProfile(bool $isReferred) :object
    {
        $profile = $this->createMock(Profile::class);

        if ($isReferred)
            $profile->method('getRewardOfReferredSite')->willReturnCallback(
                function (int $rew) { return $rew; }
            );
        else
            $profile->method('getRewardOfReferredSite')->willReturn(0);

        return $profile;
    }

    private function createCryptoMock(string $crypto) :Crypto
    {
        $cryptoMock = $this->createMock(Crypto::class);
        $cryptoMock->method('getSymbol')->willReturn($crypto);
        return $cryptoMock;
    }
}

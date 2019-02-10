<?php

namespace App\Tests\DataSource;

use App\DataSource\PoolFetcher\PoolFetcherInterface;
use App\DataSource\PoolSiteDataSource;
use App\Entity\Crypto;
use App\MiningInfo\SitePoolData;
use App\MiningInfo\SitePoolDataContainer;
use PHPUnit\Framework\TestCase;

class PoolSiteDataSourceTest extends TestCase
{
    public function testGetOneSiteData(): void
    {
        $siteKey = 'asfa634kqjhas';
        $poolDataA = $this->createMock(SitePoolDataContainer::class);
        $poolDataB = $this->createMock(SitePoolDataContainer::class);

        $poolFetcher = $this->createMock(PoolFetcherInterface::class);
        $poolFetcher->method('getOneSiteData')->willReturnCallback(
            function (string $siteKeyInput) use ($siteKey, $poolDataA, $poolDataB): SitePoolDataContainer {
                return $siteKeyInput == $siteKey ? $poolDataA : $poolDataB;
            }
        );

        $dataSource = new PoolSiteDataSource($poolFetcher);

        $this->assertNotSame($poolDataB, $dataSource->getOneSiteData($siteKey, $this->createCryptoMock('xmr')));
    }

    private function createCryptoMock(string $crypto): Crypto
    {
        $cryptoMock = $this->createMock(Crypto::class);
        $cryptoMock->method('getSymbol')->willReturn($crypto);
        return $cryptoMock;
    }
}

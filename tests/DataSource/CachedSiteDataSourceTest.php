<?php

namespace App\Tests\DataSource;

use App\DataSource\CachedSiteDataSource;
use App\DataSource\SiteDataSource;
use App\Entity\Crypto;
use App\MiningInfo\SitePoolData;
use App\MiningInfo\SitePoolDataContainer;
use PHPUnit\Framework\TestCase;
use function rand;

class CachedSiteDataSourceTest extends TestCase
{
    public function testGetOneSiteDataReturnsSameAsInnerDataSource(): void
    {
        $innerContainer = new SitePoolDataContainer();
        $innerData = new SitePoolData(rand(1, 10), rand(1, 10));
        $innerContainer->set('random', $innerData);

        $innerSource = $this->createMock(SiteDataSource::class);
        $innerSource->method('getOneSiteData')
            ->with('site-key')
            ->willReturn($innerContainer);

        $cachedSource = new CachedSiteDataSource($innerSource);
        $this->assertEquals(
            $innerContainer,
            $cachedSource->getOneSiteData('site-key', $this->createCryptoMock('xmr'))
        );
    }

    public function testGetOneSiteDataDoesNotReaskInnerDataSource(): void
    {
        $innerContainer = new SitePoolDataContainer();
        $innerData = new SitePoolData(rand(1, 10), rand(1, 10));
        $innerContainer->set('random', $innerData);

        $innerSource = $this->createMock(SiteDataSource::class);
        $innerSource->method('getOneSiteData')
            ->willReturn($innerContainer);
        $innerSource->expects($this->once())->method('getOneSiteData');

        $cachedSource = new CachedSiteDataSource($innerSource);
        for ($i = 0; $i < 10; $i++)
            $cachedSource->getOneSiteData('site-key', $this->createCryptoMock('xmr'));
    }

    public function testGetOneSiteDataCachesCorrectData(): void
    {
        $innerContainerA = new SitePoolDataContainer();
        $innerDataA = new SitePoolData(rand(1, 10), rand(1, 10));
        $innerContainerA->set('rand', $innerDataA);


        $innerContainerB = new SitePoolDataContainer();
        $innerDataB = new SitePoolData(rand(1, 10), rand(1, 10));
        $innerContainerB->set('rand', $innerDataB);


        $innerSource = $this->createMock(SiteDataSource::class);
        $innerSource->method('getOneSiteData')
            ->withConsecutive(
                [ 'site-a', $this->createCryptoMock('xmr') ],
                [ 'site-b', $this->createCryptoMock('web') ]
            )->willReturn($innerContainerA, $innerContainerB);

        $innerSource->expects($this->exactly(2))->method('getOneSiteData');

        $cachedSource = new CachedSiteDataSource($innerSource);
        for ($i = 0; $i < 10; $i++) {
            $cachedSource->getOneSiteData('site-a', $this->createCryptoMock('xmr'));
            $cachedSource->getOneSiteData('site-b', $this->createCryptoMock('web'));
        }


        $this->assertEquals($innerContainerA, $cachedSource->getOneSiteData('site-a', $this->createCryptoMock('xmr')));
        $this->assertEquals($innerContainerB, $cachedSource->getOneSiteData('site-b', $this->createCryptoMock('web')));
    }

    private function createCryptoMock(string $crypto): Crypto
    {
        $cryptoMock = $this->createMock(Crypto::class);
        $cryptoMock->method('getSymbol')->willReturn($crypto);
        return $cryptoMock;
    }
}

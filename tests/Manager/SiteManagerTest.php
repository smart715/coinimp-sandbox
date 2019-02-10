<?php

namespace App\Tests\Manager;

use App\Entity\Crypto;
use App\Entity\Profile;
use App\Entity\Site;
use App\Fetcher\ProfileFetcherInterface;
use App\Manager\SiteManager;
use App\MiningInfo\MiningInfoInterface;
use App\OrmAdapter\OrmAdapterInterface;
use App\SiteUpdater\SiteUpdaterInterface;
use GenPhrase\Password;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class SiteManagerTest extends TestCase
{
    public function testGetAllUpdateWordsOnEachProfileSite(): void
    {
        $expectedWords = 'abcdef';

        $siteA = $this->createSiteMock($expectedWords);
        $siteB = $this->createSiteMock($expectedWords);

        $siteManager = new SiteManager(
            $this->createWordsGenerator($expectedWords),
            $this->createMock(OrmAdapterInterface::class),
            $this->createMock(SiteUpdaterInterface::class),
            $this->createProfileFetcher([ $siteA, $siteB ])
        );

        $siteManager->getAll($this->createMock(Crypto::class));
    }

    /**
     * @param string $expectedWords
     * @return Site
     */
    private function createSiteMock(string $expectedWords): object
    {
        $site = $this->createMock(Site::class);
        $site->method('getWords')->willReturn('');
        $site->expects($this->once())->method('setWords')->with($expectedWords);

        return $site;
    }

    /**
     * @param string $expectedWords
     * @return Password
     */
    private function createWordsGenerator(string $expectedWords): object
    {
        $phraseGenerator = $this->createMock(\GenPhrase\Password::class);
        $phraseGenerator->method('generate')->willReturn($expectedWords);

        return $phraseGenerator;
    }

    /**
     * @param array $sites
     * @return Profile
     */
    private function createProfileFetcher(array $sites): object
    {
        $profile = $this->createMock(Profile::class);
        $profile->method('getSites')->willReturn($sites);
        $profileFetcher = $this->createMock(ProfileFetcherInterface::class);
        $profileFetcher->method('fetchProfile')->willReturn($profile);

        return $profileFetcher;
    }
}

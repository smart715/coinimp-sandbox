<?php

namespace App\Manager;

use App\Entity\Crypto;
use App\Entity\Profile;
use App\Entity\Site;
use App\Fetcher\ProfileFetcherInterface;
use App\Fetcher\StaticProfileFetcher;
use App\Manager\Model\TotalStats;
use App\OrmAdapter\OrmAdapterInterface;
use App\Repository\SiteRepository;
use App\SiteUpdater\SiteUpdaterInterface;
use Exception;
use GenPhrase\Password as PhraseGenerator;

class SiteManager implements SiteManagerInterface
{
    private const ENTROPY_VALUE = 30.0;

    /** @var SiteRepository */
    private $repository;

    /** @var PhraseGenerator */
    private $phraseGenerator;

    /** @var OrmAdapterInterface */
    private $orm;

    /** @var SiteUpdaterInterface */
    private $siteUpdater;

    /** @var ProfileFetcherInterface */
    private $profileFetcher;

    public function __construct(
        PhraseGenerator $phraseGenerator,
        OrmAdapterInterface $ormAdapter,
        SiteUpdaterInterface $siteUpdater,
        ProfileFetcherInterface $profileFetcher
    ) {
        $this->phraseGenerator = $phraseGenerator;
        $this->siteUpdater = $siteUpdater;
        $this->orm = $ormAdapter;
        $this->repository = $this->orm->getRepository(Site::class);
        $this->profileFetcher = $profileFetcher;
    }

    public function createSite(Crypto $crypto): Site
    {
        return Site::createVisible($this->getProfile(), $this->generateWords(), $crypto);
    }

    /**
     * @return Site[]
     * @throws Exception
     */
    public function getAll(Crypto $crypto): array
    {
        foreach ($this->getProfile()->getSites($crypto) as $site) {
            $this->updateSite($site);
            if ('' !== $site->getWords())
                continue;

            $site->setWords($this->generateWords());
        }
        $this->orm->flush();
        return $this->getProfile()->getSites($crypto);
    }

    public function getByWords(string $words): ?Site
    {
        return $this->getUpdatedSite(
            $this->repository->findByProfileAndWords($this->getProfile(), $words)
        );
    }

    public function getLocalMiner(Crypto $crypto): Site
    {
        return $this->getUpdatedSite(
            $this->repository->findLocalMinerByProfile($this->getProfile(), $crypto)
        ) ?? $this->createLocalMiner($crypto);
    }

    public function setProfile(Profile $profile): void
    {
        $this->profileFetcher = new StaticProfileFetcher($profile);
    }

    public function updateSite(Site $site): void
    {
        $this->siteUpdater->updateSite($site);
    }

    public function updateReferralSites(Crypto $crypto): void
    {
        foreach ($this->getProfile()->getReferralSites($crypto) as $site)
            $this->updateSite($site);
    }

    public function deleteSite(Site $site): void
    {
        $this->getProfile()->preserveReward($this->getUpdatedSite($site));
        $this->orm->remove($site);
        $this->orm->flush();
    }

    public function editSite(Site $site, string $newName): void
    {
        $site->setName($newName);
        $this->orm->flush();
    }

    public function getTotalInfo(Crypto $crypto): TotalStats
    {
        $totalReward = $this->getProfile()->getPreservedReward($crypto)
            + $this->getProfile()->calculateIncomeReferralReward($crypto);
        $totalHashRate = 0;
        $totalHashes = $this->getProfile()->getPreservedHashes($crypto);

        foreach ($this->getAll($crypto) as $site) {
            $totalHashes += $site->getHashesCount();
            $totalHashRate += $site->getHashRate();
            $totalReward += $site->getReward();
        }

        $pendingReward = $totalReward - $this->getProfile()->getPayedReward($crypto);

        return new TotalStats($totalHashRate, $totalHashes, $pendingReward, $totalReward);
    }

    public function findByKey(string $key): ?Site
    {
        return $this->repository->findByProfileAndKey(
            $this->getProfile(),
            $key
        );
    }

    private function getProfile(): Profile
    {
        return $this->profileFetcher->fetchProfile();
    }

    private function generateWords(): string
    {
        $this->phraseGenerator->alwaysUseSeparators(true);
        $this->phraseGenerator->setSeparators('-');
        $this->phraseGenerator->disableWordModifier(true);
        return $this->phraseGenerator->generate(self::ENTROPY_VALUE);
    }

    private function createLocalMiner(Crypto $crypto): Site
    {
        $site = Site::createLocalMiner($this->getProfile(), $crypto);
        $this->orm->persist($site);
        $this->orm->flush();
        return $site;
    }

    private function getUpdatedSite(?Site $site): ?Site
    {
        if (is_null($site))
            return $site;

        $this->updateSite($site);
        return $site;
    }
}

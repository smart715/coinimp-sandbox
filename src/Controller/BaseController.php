<?php

namespace App\Controller;

use App\Crypto\CryptoFactoryInterface;
use App\DataSource\FetchException;
use App\Entity\Crypto;
use App\Entity\Profile;
use App\Entity\Site;
use App\Manager\ApiKeyManagerInterface;
use App\Manager\ProfileManagerInterface;
use App\Manager\SiteManagerInterface;
use App\MiningInfo\Factory\MiningInfoFactory;
use App\MiningInfo\GlobalPoolDataInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class BaseController extends AbstractController
{
    /** @var EntityManagerInterface */
    private $orm;

    /** @var ProfileManagerInterface */
    private $profileManager;

    /** @var ApiKeyManagerInterface */
    private $keyManager;

    /** @var SiteManagerInterface */
    private $siteManager;

    /** @var NormalizerInterface */
    private $normalizer;

    /** @var MiningInfoFactory */
    private $miningInfoFactory;

    /** @var CryptoFactoryInterface */
    private $cryptoFactory;

    public function __construct(
        ApiKeyManagerInterface $keyManager,
        EntityManagerInterface $orm,
        ProfileManagerInterface $profileManager,
        SiteManagerInterface $siteManager,
        NormalizerInterface $normalizer,
        MiningInfoFactory $miningInfoFactory,
        CryptoFactoryInterface $cryptoFactory
    ) {
        $this->orm = $orm;
        $this->profileManager = $profileManager;
        $this->keyManager = $keyManager;
        $this->siteManager = $siteManager;
        $this->normalizer = $normalizer;
        $this->miningInfoFactory = $miningInfoFactory;
        $this->cryptoFactory = $cryptoFactory;
    }

    protected function getOrm(): EntityManagerInterface
    {
        return $this->orm;
    }

    protected function getApiKeyManager(): ApiKeyManagerInterface
    {
        return $this->keyManager;
    }

    protected function getProfile(): Profile
    {
        if (is_null($this->getUser()))
            return new Profile();

        return $this->profileManager->getProfile($this->getUser()->getId());
    }

    protected function getProfileByEmail(string $email): Profile
    {
        return $this->profileManager->findByEmail($email);
    }

    protected function createProfileReferralCode(): String
    {
        return $this->profileManager->createReferralCode($this->getProfile());
    }

    protected function getSiteManager(): SiteManagerInterface
    {
        return $this->siteManager;
    }

    protected function getCrypto(string $symbol): Crypto
    {
        return $this->cryptoFactory->create($symbol);
    }

    protected function getDefaultCrypto(): Crypto
    {
        return $this->getCrypto(
            array_values($this->cryptoFactory->getSupportedList())[0]
        );
    }

    protected function createSiteEntry(Site $site): array
    {
        $this->getSiteManager()->updateSite($site);

        return [
            'name' => $site->getName(),
            'hashesRate' => $site->getHashRate(),
            'hashesTotal' => $site->getHashesCount(),
            'reward' => $site->getReward(),
            'words' => $site->getWords(),
            'siteKey' => $site->getKey(),
            'isVisible' => $site->isVisible(),
            'editUrl' => $this->generateUrl(
                'site_edit',
                ['crypto' => $site->getCrypto()->getSymbol(), 'siteWords' => $site->getWords()]
            ),
            'editUrlE' => $this->generateUrl(
                'site_edit',
                ['crypto' => $site->getCrypto()->getSymbol(), 'siteWords' => $site->getWords(), 'edit' => 'true', 'error' => 'true']
            ),
            'deleteUrl' => $this->generateUrl(
                'site_delete',
                ['crypto' => $site->getCrypto()->getSymbol(), 'siteWords' => $site->getWords()]
            ),
            'users' => $this->normalizer->normalize($site->getUsers(), 'json', [
                'groups' => ['default'],
            ]),
        ];
    }

    protected function createTotalStats(Crypto $crypto): array
    {
        $totalInfo = $this->getSiteManager()->getTotalInfo($crypto);

        return [
            'totalHashes' => $totalInfo->getHashes(),
            'totalHashRate' => $totalInfo->getRate(),
            'pendingBalance' => $totalInfo->getPendingReward(),
        ];
    }

    protected function createPoolStats(Crypto $crypto): array
    {
        try {
            $miningInfo = $this->miningInfoFactory->getMiningInfo($crypto);
            $globalData = $miningInfo->getGlobalPoolData();
        } catch (FetchException $e) {
            return $this->createFailPoolStats();
        }
        return $this->createSuccessPoolStats($globalData);
    }

    private function createSuccessPoolStats(GlobalPoolDataInterface $globalData): array
    {
        return [
            'payoutPerMillion' => $globalData->getPayoutPerMillion(),
            'difficulty' => $globalData->getDifficulty(),
            'blockReward' => $globalData->getBlockReward(),
            'orphanBlocks' => $globalData->getOrphanFeePercentage(),
            'payoutPercentage' => $globalData->getPayoutPercentage(),
            'payoutWithoutAdsPercentage' => $globalData->getPayoutWithoutAdsPercentage(),
            'isAlive' => true,
        ];
    }

    private function createFailPoolStats(): array
    {
        return [
            'payoutPerMillion' => 0,
            'difficulty' => 0,
            'blockReward' => 0,
            'orphanBlocks' => 0,
            'payoutPercentage' => 0,
            'payoutWithoutAdsPercentage' => 0,
            'isAlive' => false,
        ];
    }
}

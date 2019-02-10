<?php

namespace App\MiningInfo;

use App\DataSource\NetworkDataSource\NetworkDataSource;
use App\DataSource\SiteDataSource;
use App\Entity\Crypto;
use App\MiningInfo\GlobalPoolData\GlobalPoolData;
use Craue\ConfigBundle\Util\Config;
use Exception;
use Psr\Log\LoggerInterface;

class MiningInfo implements MiningInfoInterface
{
    /** @var Config */
    private $config;

    /** @var LoggerInterface */
    private $logger;

    /** @var PayoutPercentagesConfig */
    private $percentages;

    /** @var NetworkDataSource */
    private $networkDataSource;

    /** @var SiteDataSource */
    private $siteDataSource;

    /** @var Crypto */
    private $crypto;

    /** @var null|GlobalPoolData */
    private $difficultyInfo = null;

    public function __construct(
        NetworkDataSource $networkDataSource,
        SiteDataSource $siteDataSource,
        LoggerInterface $logger,
        Config $config,
        PayoutPercentagesConfig $percentages,
        Crypto $crypto
    ) {
        $this->networkDataSource = $networkDataSource;
        $this->siteDataSource = $siteDataSource;
        $this->config = $config;
        $this->logger = $logger;
        $this->percentages = $percentages;
        $this->crypto = $crypto;
    }

    public function getGlobalPoolData(): GlobalPoolDataInterface
    {
        if (!is_null($this->difficultyInfo))
            return $this->difficultyInfo;

        $this->difficultyInfo = new GlobalPoolData(
            $this->calculateDifficultyWithFee(),
            $this->getMinimalBlockReward(),
            $this->calculatePayoutPercentage(),
            $this->calculatePayoutWithoutAdsPercentage(),
            $this->percentages->getReferralPercentage(),
            $this->percentages->getOrphanFeePercentage()
        );

        return $this->difficultyInfo;
    }

    public function getSitePoolData(string $siteKey): SitePoolDataContainer
    {
        return $this->siteDataSource->getOneSiteData($siteKey, $this->crypto);
    }

    public function getHiddenFee(): float
    {
        return $this->percentages->getHiddenFee() / 100;
    }

    public function getCrypto(): Crypto
    {
        return $this->crypto;
    }

    private function calculateDifficultyWithFee(): float
    {
        return $this->networkDataSource->getNetworkDifficulty($this->crypto)
            * (1 + ($this->percentages->getDifficultyFee() / 100));
    }

    private function calculatePayoutPercentage(): float
    {
        return round(100 - $this->percentages->getPayoutFee(), 0);
    }

    private function calculatePayoutWithoutAdsPercentage(): float
    {
        return round(100 - $this->percentages->getPayoutFeeWithoutAds(), 0);
    }

    private function getMinimalBlockReward(): int
    {
        $ourReward = intval($this->config->get($this->getCrypto()->getSymbol().'-minimal-block-reward'));
        $remoteReward = $this->tryGetLastBlockReward();

        if ($remoteReward <= 0)
            return $ourReward;

        if ($ourReward < $remoteReward)
            return $ourReward;

        $this->config->set($this->getCrypto()->getSymbol().'-minimal-block-reward', (string)$remoteReward);
        return $remoteReward;
    }

    private function tryGetLastBlockReward(): int
    {
        try {
            return $this->networkDataSource->getLastBlockReward($this->crypto);
        } catch (\Throwable $e) {
            $this->logger->error($e->getMessage());
            return -1;
        }
    }
}

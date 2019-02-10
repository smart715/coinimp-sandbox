<?php

namespace App\SiteUpdater;

use App\Entity\Crypto;
use App\Entity\Site;
use App\MiningInfo\Factory\MiningInfoFactory;
use Psr\Log\LoggerInterface;

class OneByOneUpdater implements SiteUpdaterInterface
{
    /** @var MiningInfoFactory */
    private $miningInfoFactory;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        MiningInfoFactory $miningInfoFactory,
        LoggerInterface $logger
    ) {
        $this->miningInfoFactory = $miningInfoFactory;
        $this->logger = $logger;
    }

    public function updateSite(Site $site): void
    {
        try {
            $miningInfo = $this->miningInfoFactory->getMiningInfo($site->getCrypto());

            $site->updateWithPoolData(
                $miningInfo->getSitePoolData($site->getKey()),
                $miningInfo->getGlobalPoolData(),
                $miningInfo->getHiddenFee()
            );
        } catch (\Throwable $e) {
            $this->logger->error(
                'Failed to update site: '.$e->getMessage()
            );
        }
    }
}

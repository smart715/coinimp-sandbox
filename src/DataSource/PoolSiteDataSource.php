<?php

namespace App\DataSource;

use App\DataSource\PoolFetcher\PoolFetcherInterface;
use App\Entity\Crypto;
use App\MiningInfo\SitePoolDataContainer;

class PoolSiteDataSource implements SiteDataSource
{
    /** @var PoolFetcherInterface */
    private $poolFetcher;

    public function __construct(PoolFetcherInterface $poolFetcher)
    {
        $this->poolFetcher = $poolFetcher;
    }

    public function getOneSiteData(string $siteKey, Crypto $crypto): SitePoolDataContainer
    {
        return $this->poolFetcher->getOneSiteData($siteKey, $crypto);
    }
}

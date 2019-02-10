<?php

namespace App\DataSource\PoolFetcher;

use App\Entity\Crypto;
use App\MiningInfo\SitePoolDataContainer;
use Exception;

interface PoolFetcherInterface
{
    public function getOneSiteData(string $siteKey, Crypto $crypto): SitePoolDataContainer;
}

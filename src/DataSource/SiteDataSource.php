<?php

namespace App\DataSource;

use App\Entity\Crypto;
use App\MiningInfo\SitePoolData;
use App\MiningInfo\SitePoolDataContainer;

interface SiteDataSource
{
    public function getOneSiteData(string $siteKey, Crypto $crypto): SitePoolDataContainer;
}

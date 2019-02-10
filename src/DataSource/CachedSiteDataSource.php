<?php

namespace App\DataSource;

use App\Entity\Crypto;
use App\MiningInfo\SitePoolDataContainer;

class CachedSiteDataSource implements SiteDataSource
{
    /** @var SiteDataSource */
    private $innerSource;

    /** @var mixed[] */
    private $cache = [];

    public function __construct(SiteDataSource $innerSource)
    {
        $this->innerSource = $innerSource;
    }


    public function getOneSiteData(string $siteKey, Crypto $crypto): SitePoolDataContainer
    {
        if (isset($this->cache[$siteKey]))
            return $this->cache[$siteKey];

        $this->cache[$siteKey] = $this->innerSource->getOneSiteData($siteKey, $crypto);
        return $this->cache[$siteKey];
    }
}

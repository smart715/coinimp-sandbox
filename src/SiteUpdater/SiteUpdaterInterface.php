<?php

namespace App\SiteUpdater;

use App\Entity\Site;

interface SiteUpdaterInterface
{
    public function updateSite(Site $site): void;
}

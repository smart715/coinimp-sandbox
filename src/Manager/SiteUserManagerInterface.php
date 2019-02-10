<?php

namespace App\Manager;

use App\Entity\Site;
use App\Entity\SiteUser;

interface SiteUserManagerInterface
{
    public function makeWithdraw(SiteUser $siteUser, int $amount): void;
    public function findUserByName(Site $siteUser, string $name): ?SiteUser;
}

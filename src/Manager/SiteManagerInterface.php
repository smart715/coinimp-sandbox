<?php

namespace App\Manager;

use App\Entity\Crypto;
use App\Entity\Profile;
use App\Entity\Site;
use App\Manager\Model\TotalStats;

interface SiteManagerInterface
{
    public function setProfile(Profile $profile): void;
    public function createSite(Crypto $crypto): Site;
    public function getByWords(string $words): ?Site;
    public function getLocalMiner(Crypto $crypto): Site;
    public function updateSite(Site $site): void;
    public function updateReferralSites(Crypto $crypto): void;
    public function deleteSite(Site $site): void;
    public function editSite(Site $site, string $newName): void;
    public function getTotalInfo(Crypto $crypto): TotalStats;
    public function findByKey(string $key): ?Site;

    /**
     * @return Site[]
     */
    public function getAll(Crypto $crypto): array;
}

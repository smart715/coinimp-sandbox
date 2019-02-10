<?php

namespace App\Repository;

use App\Entity\Crypto;
use App\Entity\Profile;
use App\Entity\Site;
use Doctrine\ORM\EntityRepository;

class SiteRepository extends EntityRepository
{
    public function findByProfileAndKey(Profile $profile, string $key): ?Site
    {
        /** @var Site|null $site */
        $site = $this->findOneBy([
            'profile' => $profile->getId(),
            'miningKey' => $key,
        ]);
        return $site;
    }

    public function findByProfileAndWords(Profile $profile, string $words): ?Site
    {
        /** @var Site|null $site */
        $site = $this->findOneBy([
            'profile' => $profile->getId(),
            'words' => $words,
        ]);
        return $site;
    }

    public function findLocalMinerByProfile(Profile $profile, Crypto $crypto): ?Site
    {
        /** @var Site|null $site */
        $site = $this->findOneBy([
            'profile' => $profile->getId(),
            'type' => SITE::TYPE_MINER,
            'crypto' => $crypto,
        ]);
        return $site;
    }
}

<?php

namespace App\Manager;

use App\Entity\Site;
use App\Entity\SiteUser;
use App\OrmAdapter\OrmAdapterInterface;

class SiteUserManager implements SiteUserManagerInterface
{
    /** @var OrmAdapterInterface */
    private $orm;

    public function __construct(OrmAdapterInterface $ormAdapter)
    {
        $this->orm = $ormAdapter;
    }

    public function makeWithdraw(SiteUser $siteUser, int $amount): void
    {
        $balance = $siteUser->getHashes() - $siteUser->getWithdrawn();

        assert($balance > $amount);

        $siteUser->withdraw($amount);

        $this->orm->persist($siteUser);
        $this->orm->flush();
    }

    public function findUserByName(Site $site, string $name): ?SiteUser
    {
        foreach ($site->getUsers() as $user)
            if ($user->getName() == $name)
                return $user;

        return null;
    }
}

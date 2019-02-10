<?php

namespace App\Repository;

use App\Entity\Airdrop;
use App\Entity\Profile;
use Doctrine\ORM\EntityRepository;

class AirdropRepository extends EntityRepository
{
    public function findByCode(string $code): ?Airdrop
    {
        return $this->findOneBy([
            'code' => $code,
        ]);
    }

    public function findByProfile(Profile $profile): ?Airdrop
    {
        return $this->findOneBy([
            'profile' => $profile,
        ]);
    }

    public function getFreeAirdrop(): ?Airdrop
    {
        return $this->findOneBy([
            'profile' => null,
            'isActive' => true,
        ]);
    }
}

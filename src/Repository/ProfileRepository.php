<?php

namespace App\Repository;

use App\Entity\Profile;
use App\Entity\User;
use Doctrine\ORM\EntityRepository;

class ProfileRepository extends EntityRepository
{
    public function findByUserId(string $userId): ?Profile
    {
        /** @var Profile|null $profile */
        $profile = $this->findOneBy([ 'user' => $userId ]);
        return $profile;
    }

    public function findReferrer(string $referralCode): ?Profile
    {
        /** @var Profile|null $profile */
        $profile = $this->findOneBy([ 'referralCode' => $referralCode ]);
        return $profile;
    }
}

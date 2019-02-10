<?php

namespace App\Repository;

use App\Entity\User;
use Doctrine\ORM\EntityRepository;

class UserRepository extends EntityRepository
{
    public function findByEmail(string $email): ?User
    {
        /** @var User|null $user */
        $user = $this->findOneBy([ 'email' => $email ]);
        return $user;
    }

    public function getRegisteredUsersCount(): int
    {
        $cachePeriod = 3600;  // 1 hour
        return $this->createQueryBuilder('user')
            ->select('count(user.id)')
            ->getQuery()
            ->useResultCache(true, $cachePeriod, 'usersCountCache')
            ->getSingleScalarResult();
    }

    public function clearUserCountCache(): void
    {
        $cacheDriver = $this->getEntityManager()->getConfiguration()->getResultCacheImpl();
        null === $cacheDriver ?: $cacheDriver->delete('usersCountCache');
    }
}

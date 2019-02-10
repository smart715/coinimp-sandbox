<?php

namespace App\Repository;

use Doctrine\ORM\EntityRepository;

class WalletImpRepository extends EntityRepository
{
    public function getTotalSoldImp(): int
    {
        $cachePeriod = 20;  // 20 sec
        return $this->createQueryBuilder('wallet')
            ->select('sum(wallet.actualPaid)')
            ->getQuery()
            ->useResultCache(true, $cachePeriod, 'totalSoldImp')
            ->getSingleScalarResult();
    }
}

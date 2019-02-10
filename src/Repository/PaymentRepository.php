<?php

namespace App\Repository;

use App\Entity\Payment\Status;
use Doctrine\ORM\EntityRepository;

class PaymentRepository extends EntityRepository
{
    public const CACHE_PERIOD = 3600;  // 1 hour
    public const CACHE_SUFFIX_ID = '_PaidAmountCache';

    public function getTotalPaidBySymbolId(
        int $symbolId = 1,
        bool $removeFees = false
    ): string {
        $resultCacheId = $symbolId . self::CACHE_SUFFIX_ID;

        $select = 'payment.amount' . ($removeFees ?  ' - payment.fee' : '');

        $totalPaid = $this->createQueryBuilder('payment')
            ->select('sum(' . $select . ')')
            ->where('payment.crypto = :cryptoId')
            ->andWhere('payment.status = :status')
            ->setParameter('cryptoId', $symbolId)
            ->setParameter('status', Status::PAID)
            ->getQuery()
            ->useResultCache(true, self::CACHE_PERIOD, $resultCacheId)
            ->getSingleScalarResult();

        return $totalPaid ?? "0";
    }

    public function clearTotalPaidCache(int $symbolId = 1): void
    {
        $resultCacheId = $symbolId . self::CACHE_SUFFIX_ID;

        $cacheDriver = $this->getEntityManager()->getConfiguration()->getResultCacheImpl();
        null === $cacheDriver ?: $cacheDriver->delete($resultCacheId);
    }
}

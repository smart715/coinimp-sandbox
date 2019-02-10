<?php

namespace App\Repository;

use App\Entity\Crypto;
use Doctrine\ORM\EntityRepository;

class CryptoRepository extends EntityRepository
{
    public function findBySymbol(string $symbol): ?Crypto
    {
        return $this->findOneBy(['symbol' => $symbol]);
    }
}

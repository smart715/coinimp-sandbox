<?php

namespace App\Manager;

use App\Entity\Crypto;

interface CryptoManagerInterface
{
    public function findBySymbol(string $name): ?Crypto;

    /** @return Crypto[] */
    public function getAll(): array;
}

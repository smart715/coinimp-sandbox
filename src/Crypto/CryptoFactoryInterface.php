<?php

namespace App\Crypto;

use App\Entity\Crypto;

interface CryptoFactoryInterface
{
    public function create(string $symbol): Crypto;

    /**
     * @return string[]
     */
    public function getSupportedList(): array;
}

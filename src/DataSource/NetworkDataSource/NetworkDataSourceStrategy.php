<?php

namespace App\DataSource\NetworkDataSource;

use App\Entity\Crypto;

interface NetworkDataSourceStrategy
{
    public function getNetworkDifficulty(): int;

    public function getLastBlockReward(): int;

    public function isValidCrypto(Crypto $crypto): bool;
}

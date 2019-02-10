<?php

namespace App\DataSource\NetworkDataSource;

use App\Entity\Crypto;

interface NetworkDataSource
{
    public function getNetworkDifficulty(Crypto $crypto): int;

    public function getLastBlockReward(Crypto $crypto): int;
}

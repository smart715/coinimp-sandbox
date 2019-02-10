<?php

namespace App\Manager;

use App\Entity\WalletImp\WalletImp;

interface WalletImpTransactionManagerInterface
{
    public function addWalletTransaction(
        WalletImp $wallet,
        string $type,
        float $amount,
        string $name,
        ?string $description = null,
        array $data = []
    ): void;
}

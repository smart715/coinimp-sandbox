<?php

namespace App\Crypto\Updater;

use App\Entity\Crypto;

interface CryptoUpdaterInterface
{
    public function update(Crypto &$crypto): void;
}

<?php

namespace App\Crypto\Updater;

use App\Entity\Crypto;
use App\Manager\CryptoManagerInterface;
use App\OrmAdapter\OrmAdapterInterface;

class CryptoUpdater implements CryptoUpdaterInterface
{
    /** @var OrmAdapterInterface */
    private $orm;

    public function __construct(OrmAdapterInterface $ormAdapter)
    {
        $this->orm = $ormAdapter;
    }

    public function update(Crypto &$crypto): void
    {
        /** @var Crypto|null $existedCrypto */
        $existedCrypto = $this->orm->getRepository(Crypto::class)->findBySymbol($crypto->getSymbol());

        if ($existedCrypto) {
            $existedCrypto->setFee($crypto->getFee());
            $existedCrypto->setMinimalPayout($crypto->getMinimalPayout());
            $existedCrypto->setWalletLengths($crypto->getWalletLengths());
            $existedCrypto->setExplorerUrl($crypto->getExplorerUrl());
            $crypto = $existedCrypto;
        }

        $this->orm->persist($crypto);
        $this->orm->flush();
    }
}

<?php

namespace App\Manager;

use App\Crypto\CryptoFactoryInterface;
use App\Entity\Crypto;
use App\OrmAdapter\OrmAdapterInterface;
use App\Repository\CryptoRepository;

class CryptoManager implements CryptoManagerInterface
{
    /** @var CryptoRepository $repository */
    private $repository;

    /** @var CryptoFactoryInterface */
    private $cryptoFactory;

    public function __construct(OrmAdapterInterface $ormAdapter, CryptoFactoryInterface $cryptoFactory)
    {
        $this->repository = $ormAdapter->getRepository(Crypto::class);
        $this->cryptoFactory = $cryptoFactory;
    }

    public function findBySymbol(string $symbol): ?Crypto
    {
        return $this->repository->findBySymbol($symbol);
    }

    /** @inheritdoc */
    public function getAll(): array
    {
        return array_map(function (string $symbol): Crypto {
            return $this->cryptoFactory->create($symbol);
        }, $this->cryptoFactory->getSupportedList());
    }
}

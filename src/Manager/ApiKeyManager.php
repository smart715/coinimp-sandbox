<?php

namespace App\Manager;

use App\Entity\ApiKey;
use App\OrmAdapter\OrmAdapterInterface;
use App\Repository\ApiKeyRepository;

class ApiKeyManager implements ApiKeyManagerInterface
{
    /** @var ApiKeyRepository */
    private $repository;

    /** @var OrmAdapterInterface */
    private $ormAdapter;

    public function __construct(OrmAdapterInterface $ormAdapter)
    {
        $this->ormAdapter = $ormAdapter;
        $this->repository = $this->ormAdapter->getRepository(ApiKey::class);
    }

    public function findApiKey(string $keyValue): ?ApiKey
    {
        return $this->repository->findApiKey($keyValue);
    }
}

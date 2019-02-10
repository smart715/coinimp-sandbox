<?php

namespace App\OrmAdapter;

use App\OrmAdapter\WriteStrategy\IWriteStrategy;
use Doctrine\Common\Persistence\ObjectRepository;
use Doctrine\ORM\EntityManagerInterface;

class OrmAdapter implements OrmAdapterInterface
{
    /** @var EntityManagerInterface */
    private $orm;

    /** @var IWriteStrategy */
    private $writeStrategy;

    public function __construct(
        EntityManagerInterface $orm,
        IWriteStrategy $writeStrategy
    ) {
        $this->orm = $orm;
        $this->writeStrategy = $writeStrategy;
    }

    public function flush(): void
    {
        $this->writeStrategy->flush($this->orm);
    }

    /** {@inheritdoc} */
    public function persist($something): void
    {
        $this->writeStrategy->persist($something, $this->orm);
    }

    /** {@inheritdoc} */
    public function remove($something): void
    {
        $this->writeStrategy->remove($something, $this->orm);
    }

    public function getRepository(string $className): ObjectRepository
    {
        return $this->orm->getRepository($className);
    }

    /** {@inheritdoc} */
    public function find(string $className, int $id)
    {
        return $this->orm->find($className, $id);
    }
}

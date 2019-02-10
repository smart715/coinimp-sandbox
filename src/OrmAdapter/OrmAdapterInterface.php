<?php

namespace App\OrmAdapter;

use Doctrine\Common\Persistence\ObjectRepository;

interface OrmAdapterInterface
{
    public function flush(): void;

    public function persist(?object $something): void;

    public function remove(?object $something): void;

    public function getRepository(string $className): ObjectRepository;

    /**
     * @param string $className
     * @param int $id
     * @return mixed
     */
    public function find(string $className, int $id);
}

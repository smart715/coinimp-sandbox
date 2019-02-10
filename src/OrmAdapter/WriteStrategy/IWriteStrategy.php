<?php

namespace App\OrmAdapter\WriteStrategy;

use Doctrine\ORM\EntityManagerInterface;

interface IWriteStrategy
{
    public function flush(EntityManagerInterface $orm): void;

    public function persist(?object $something, EntityManagerInterface $orm): void;

    public function remove(?object $something, EntityManagerInterface $orm): void;
}

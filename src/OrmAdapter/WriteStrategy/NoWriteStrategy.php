<?php

namespace App\OrmAdapter\WriteStrategy;

use Doctrine\ORM\EntityManagerInterface;

class NoWriteStrategy implements IWriteStrategy
{
    public function flush(EntityManagerInterface $orm): void
    {
    }

    /** {@inheritdoc} */
    public function persist($something, EntityManagerInterface $orm): void
    {
    }

    /** {@inheritdoc} */
    public function remove($something, EntityManagerInterface $orm): void
    {
    }
}

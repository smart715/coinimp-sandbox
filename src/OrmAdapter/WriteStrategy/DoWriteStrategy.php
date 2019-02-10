<?php

namespace App\OrmAdapter\WriteStrategy;

use Doctrine\ORM\EntityManagerInterface;

class DoWriteStrategy implements IWriteStrategy
{
    public function flush(EntityManagerInterface $orm): void
    {
        $orm->flush();
    }

    /** {@inheritdoc} */
    public function persist($something, EntityManagerInterface $orm): void
    {
        $orm->persist($something);
    }

    /** {@inheritdoc} */
    public function remove($something, EntityManagerInterface $orm): void
    {
        $orm->remove($something);
    }
}

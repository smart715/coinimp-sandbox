<?php

namespace App\Entity\Payment;

class Transaction
{
    private $id;

    private $hash;
    private $key;

    public function __construct(string $hash, string $key)
    {
        $this->hash = $hash;
        $this->key = $key;
    }

    public function getHash() :string
    {
        return $this->hash;
    }

    public function getKey() :string
    {
        return $this->key;
    }
}
<?php

namespace App\Entity;

use Ramsey\Uuid\Uuid;

class ApiKey
{
    /** @var int */
    private $id;

    /** @var User */
    private $user;

    /** @var string */
    private $publicKey;

    /** @var string */
    private $privateKey;

    /** @var string|null */
    private $plainPrivateKey;

    private function __construct()
    {
        $this->generateKeys();
    }

    public function getId() :int
    {
        return $this->id;
    }

    public function getPlainPrivateKey() :string
    {
        return (string)$this->plainPrivateKey;
    }

    public function getPublicKey() :string
    {
        return $this->publicKey;
    }

    public function getPrivateKey() :string
    {
        return $this->privateKey;
    }

    public function getUser() :User
    {
        return $this->user;
    }

    public function generateKeys() :void
    {
        $this->publicKey = hash('sha256', Uuid::uuid4()->toString());
        $this->plainPrivateKey = hash('sha256', Uuid::uuid4()->toString());
        $this->privateKey = password_hash($this->plainPrivateKey, PASSWORD_DEFAULT);
    }

    static public function fromNewUser(User $user) :self
    {
        $key = new ApiKey();
        $key->user = $user;
        return $key;
    }
}
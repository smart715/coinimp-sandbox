<?php

namespace App\Entity;


use Symfony\Component\Serializer\Annotation\Groups;

class SiteUser
{
    protected $id;
    /**
     * @Groups({"default", "api", "balance"})
     */
    protected $name;
    /**
     * @Groups({"default", "api", "balance"})
     */
    protected $withdrawn = 0;
    protected $site;

    /**
     * @Groups({"default", "api", "balance"})
     */
    protected $hashes = 0;
    /**
     * @Groups({"default", "api"})
     */
    private $hashRate = 0.0;

    public function __construct(string $name, Site $site)
    {
        $this->name = $name;
        $this->site = $site;
    }

    public function getId() :int
    {
        return $this->id;
    }

    public function getName() :string
    {
        return $this->name;
    }

    public function getHashes() :int
    {
        return $this->hashes;
    }

    public function getHashRate() :float
    {
        return $this->hashRate;
    }

    public function getSite() :Site
    {
        return $this->site;
    }

    public function getWithdrawn() :int
    {
        return $this->withdrawn;
    }

    public function withdraw(int $amount): void
    {
        $this->withdrawn += $amount;
    }

    public function setHashes(int $hashes): void
    {
        $this->hashes = $hashes;
    }

    public function setHashRate(float $hashRate): void
    {
        $this->hashRate = $hashRate;
    }
}
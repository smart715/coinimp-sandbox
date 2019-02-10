<?php

namespace App\Entity;

class HashesShare
{
    private $id;
    private $hashes = 0;
    private $fee;

    /** @var Site */
    private $site;

    public function __construct(Site $site, float $fee)
    {
        assert($fee >= 0.0 && $fee < 1.0);

        $this->site = $site;
        $this->fee = $fee;
    }

    public function getUserVisibleHashesCount() :int
    {
        return $this->hashes;
    }

    public function getRealHashesCount() :int
    {
        return intval(round(
            $this->hashes / $this->calculateCoefficient()
        ));
    }

    public function matches(float $fee) :bool
    {
        return $this->fee == $fee;
    }

    public function addUserVisibleHashes(int $hashes) :void
    {
        $this->hashes += $hashes;
    }

    private function calculateCoefficient() :float
    {
        return 1 - $this->fee;
    }
}
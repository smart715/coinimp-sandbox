<?php

namespace App\Entity;

class Preserve
{
    private $id;

    /** @var Profile */
    private $profile;

    private $reward;
    private $hashes;
    private $referralReward;
    private $crypto;

    public function __construct(Profile $profile, Crypto $crypto)
    {
        $this->profile = $profile;
        $this->crypto = $crypto;
        $this->reward = 0;
        $this->hashes = 0;
        $this->referralReward = 0;
    }

    public function addReward(int $reward) :void
    {
        $this->reward += $reward;
    }

    public function getReward() :int
    {
        return $this->reward;
    }

    public function addHashes(int $hashes) :void
    {
        $this->hashes += $hashes;
    }

    public function getHashes() :int
    {
        return $this->hashes;
    }

    public function addReferralReward(int $referralReward) :void
    {
        $this->referralReward += $referralReward;
    }

    public function getReferralReward() :int
    {
        return $this->referralReward;
    }

    public function getCrypto() :Crypto
    {
        return $this->crypto;
    }
}
<?php


namespace App\Entity;


use App\Entity\Crypto;
use App\Entity\Site\DefaultType;
use App\Entity\Site\LocalMinerType;
use App\Entity\Site\SiteTypeInterface;
use App\Entity\Site\UserCreatedType;
use App\MiningInfo\GlobalPoolDataInterface;
use App\MiningInfo\SitePoolData;
use App\MiningInfo\SitePoolDataContainer;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Runner\Exception;

class Site
{
    public const TYPE_USER_CREATED = 0;
    public const TYPE_DEFAULT = 1;
    public const TYPE_MINER = 2;

    private $id;
    private $words = '';
    private $name = '';
    private $miningKey = '';
    private $type = self::TYPE_USER_CREATED;
    private $reward = 0;
    private $referralReward = 0;
    private $hashRate = 0.0;

    /** @var Crypto $crypto */
    private $crypto;

    /** @var Collection */
    private $shares;
    private $users;

    /** @var Profile */
    private $profile;

    public function __construct()
    {
        $this->shares = new ArrayCollection;
        $this->users = new ArrayCollection;
    }

    public function getName() :string
    {
        return $this->name;
    }

    public function getHumanReadableName() :string
    {
        return $this->getType()->getHumanReadableName($this->name);
    }

    // this setter is required by form "Add Site"
    public function setName(string $name) :void
    {
        $this->name = $name;
    }

    public function getKey() :string
    {
        return $this->miningKey;
    }

    public function generateKey() :void
    {
        $this->miningKey = hash("sha256", openssl_random_pseudo_bytes(32));
    }

    public function isVisible() :bool
    {
        return $this->getType()->isVisible();
    }

    public function isDefault() :bool
    {
        return $this->getType()->isDefault();
    }

    public function getWords() :string
    {
        return $this->words;
    }

    public function setWords(string $words) :void
    {
        $this->words = $words;
    }

    public function updateWithPoolData(
        SitePoolDataContainer $siteData,
        GlobalPoolDataInterface $poolData,
        float $hiddenFee
    ) :void
    {
        $realHashesDifference = $siteData->getOverall()->getHashesTotal() - $this->sumRealHashes();
        if ($realHashesDifference < 0)
            return; // something weird goes on

        $hashesDifference = intval(round($realHashesDifference * (1 - $hiddenFee)));

        $this->reward += $poolData->calculateRewardFor($hashesDifference);
        $this->addReferralReward($hashesDifference, $poolData);
        $this->getCurrentShare($hiddenFee)->addUserVisibleHashes($hashesDifference);
        $this->hashRate = $siteData->getOverall()->getHashRate() * (1 - $hiddenFee);

        foreach ($siteData->getUsers() as $user)
            if (!$this->siteUserExists($user))
                $this->users->add($this->createSiteUser($user));

        foreach ($this->users as $user)
            $this->updateSiteUser($user, $siteData->get($user->getName()), $hiddenFee);
    }

    public function getCrypto() :Crypto
    {
        return $this->crypto;
    }

    public function getReward() :int
    {
        return $this->reward;
    }

    public function getReferralReward() :int
    {
        return $this->referralReward;
    }

    public function getHashesCount() :int
    {
        return $this->getHashesSum(function(HashesShare $share) :int {
            return $share->getUserVisibleHashesCount();
        });
    }

    public function getHashRate() :float
    {
        return $this->hashRate;
    }

    /**
     * @return SiteUser[]
     */
    public function getUsers() :array
    {
        return $this->users->toArray();
    }

    public static function createVisible(Profile $profile, string $words, Crypto $crypto) :Site
    {
        $site = self::create($profile, $crypto);
        $site->type = self::TYPE_USER_CREATED;
        $site->words = $words;
        return $site;
    }

    public static function createInvisible(Profile $profile, Crypto $crypto) :Site
    {
        $site = self::create($profile, $crypto);
        $site->type = self::TYPE_DEFAULT;
        $site->name = hash("sha256", openssl_random_pseudo_bytes(32));
        $site->words = $site->name;
        return $site;
    }

    public static function createLocalMiner(Profile $profile, Crypto $crypto) :Site
    {
        $site = self::createInvisible($profile, $crypto);
        $site->type = self::TYPE_MINER;
        return $site;
    }

    private static function create(Profile $profile, Crypto $crypto) :Site
    {
        $site = new self;
        $site->profile = $profile;
        $site->generateKey();
        $site->crypto = $crypto;
        return $site;
    }

    private function updateSiteUser(
        SiteUser $siteUser,
        SitePoolData $siteData,
        float $fee
    ) :void {
        $hashesData = intval(round($siteData->getHashesTotal() * (1 - $fee))) ?: $siteUser->getHashes();

        $siteUser->setHashRate($siteData->getHashRate() * (1 - $fee));
        $siteUser->setHashes($hashesData);
    }

    private function createSiteUser(string $userName) :SiteUser
    {
        $user = new SiteUser($userName, $this);
        return $user;
    }

    private function siteUserExists(string $user) :bool
    {
        return $this->users->exists(function (int $index, SiteUser $siteUser) use ($user) {
            return $user == $siteUser->getName();
        });
    }

    private function getType() :SiteTypeInterface
    {
        switch ($this->type)
        {
            case self::TYPE_USER_CREATED:
                return new UserCreatedType;
            case self::TYPE_DEFAULT:
                return new DefaultType;
            case self::TYPE_MINER:
                return new LocalMinerType;
        }
        throw new Exception('Site has invalid type "'.$this->type.'"');
    }

    private function addReferralReward(
        int $hashesDifference, GlobalPoolDataInterface $poolData) :void
    {
        $this->referralReward += $this->profile->getRewardOfReferredSite(
            $poolData->calculateReferralRewardFor($hashesDifference)
        );
    }

    private function sumRealHashes() :int
    {
        return $this->getHashesSum(function(HashesShare $share) :int {
            return $share->getRealHashesCount();
        });
    }

    private function getHashesSum(callable $which) :int
    {
        return array_sum(array_map($which, $this->shares->toArray()));
    }

    private function getCurrentShare(float $currentFee) :HashesShare
    {
        /** @var HashesShare $share */
        foreach ($this->shares as $share)
            if ($share->matches($currentFee))
                return $share;

        $newShare = new HashesShare($this, $currentFee);
        $this->shares->add($newShare);
        return $newShare;
    }
}
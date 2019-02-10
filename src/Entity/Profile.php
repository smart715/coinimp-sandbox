<?php


namespace App\Entity;

use App\Entity\Crypto;
use App\Entity\Payment\Status;
use App\Entity\User;
use App\Entity\WalletImp\WalletImp;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;
use Ramsey\Uuid\Uuid;
use DateTime;

class Profile
{
    private $id = 0;

    /** @var User|null */
    private $user;

    /** @var Collection */
    private $sites;

    /** @var Collection */
    private $payments;

    /** @var Collection */
    private $referencedProfiles;

    /** @var Profile|null */
    private $referencer;

    /** @var Collection */
    private $referencedIcoProfiles;

    /** @var Profile|null */
    private $referencerIco;

    /** @var Collection */
    private $preserves;

    /** @var Collection */
    private $wallets;

    /** @var bool */
    private $policyAccepted;

    private $referralCode = '';

    // 2 sites(local miner and default) not visible to user
    private const ADD_WEBSITES_LIMIT = 2 + 50;

    /**
     * @var  WalletImp
     */
    protected $walletImp;

    public function __construct()
    {
        $this->sites = new ArrayCollection();
        $this->payments = new ArrayCollection();
        $this->referencedProfiles = new ArrayCollection();
        $this->preserves = new ArrayCollection();
        $this->generateReferralCode();
        $this->policyAccepted = false;
        $this->walletImp = new WalletImp();
    }

    public static function fromNewUser(User $user) :self
    {
        $profile = new Profile();
        $profile->user = $user;
        return $profile;
    }

    /**
     * @return Site[]
     */
    public function getSites(Crypto $crypto) :array
    {
        $sitesByCrypto = $this->sites->filter(function (Site $site) use ($crypto) :bool {
            return $site->getCrypto()->getSymbol() == $crypto->getSymbol();
        })->toArray();

        return array_values($sitesByCrypto);
    }
	
    public function isPolicyAccepted(): bool
    {
        return $this->policyAccepted;
    }

    public function acceptPolicy (): self
    {
        $this->policyAccepted = true;
        return $this;
    }


    public function getReferralSites(Crypto $crypto) :array
    {
        $sites = [];
        foreach($this->getReferrals() as $referral)
            $sites = array_merge($sites, $referral->getSites($crypto));

        return $sites;
    }

    public function canAddSites(Crypto $crypto) :bool
    {
        return count($this->getSites($crypto)) < self::ADD_WEBSITES_LIMIT;
    }

    /**
     * @return Payment[]
     */
    public function getPayments() :array
    {
        return $this->payments->toArray();
    }

    public function getPaymentsByCrypto(Crypto $crypto) :array
    {
        return $this->payments
            ->filter(function(Payment $payment) use ($crypto) {
                return $payment->getCrypto()->getSymbol() == $crypto->getSymbol();
            })->toArray();
    }

    public function getPayedReward(Crypto $crypto) :int
    {
        $reward = 0;
        foreach ($this->getPaymentsByCrypto($crypto) as $payment)
            $reward += $payment->getEffectiveAmount();

        return $reward;
    }

    public function getDefaultSite(Crypto $crypto) :Site
    {
        foreach ($this->getSites($crypto) as $site)
            if (!$site->isVisible())
                return $site;

        $this->createDefaultSite($crypto);
        return $this->sites->last();
    }

    public function createDefaultSite(Crypto $crypto) :void
    {
        if (!$this->hasDefaultSite($crypto))
            $this->sites->add(Site::createInvisible($this, $crypto));
    }

    public function getId() :int
    {
        return $this->id;
    }

    public function getUser() :User
    {
        return $this->user;
    }

    public function getWalletAddress(Crypto $crypto) :string
    {
        return $this->findWalletByCrypto($crypto)->getAddress();
    }

    public function setWalletAddress(string $address, Crypto $crypto) :void
    {
        $wallet = $this->findWalletByCrypto($crypto);
        $wallet->setAddress($address);
    }

    private function findWalletByCrypto(Crypto $crypto) :Wallet
    {
        foreach($this->wallets->toArray() as $wallet)
            if ($wallet->getCrypto()->getSymbol() == $crypto->getSymbol())
                return $wallet;

        $newWallet = new Wallet($this, $crypto);
        $this->wallets->add($newWallet);
        return $newWallet;
    }

    public function getEmail() :string
    {
        return $this->user->getEmail();
    }

    public function getCreatedDate() :DateTime
    {
        return $this->user->getCreatedAt();
    }

    public function preserveReward(Site $site) :void
    {
        $preserve = $this->getPreserveFor($site->getCrypto());

        $preserve->addReward($site->getReward());
        $preserve->addHashes($site->getHashesCount());
        $preserve->addReferralReward($site->getReferralReward());
    }

    public function getPreservedReward(Crypto $crypto) :int
    {
        return $this->getPreserveFor($crypto)->getReward();
    }

    public function getPreservedHashes(Crypto $crypto) :int
    {
        return $this->getPreserveFor($crypto)->getHashes();
    }
    public function hasPendingPayments(Crypto $crypto) :bool
    {
        foreach ($this->getPaymentsByCrypto($crypto) as $payment)
            if ($payment->getStatus() == Status::PENDING)
                return true;

        return false;
    }

    public function referenceBy(Profile $profile) :void
    {
        $this->referencer = $profile;
    }

    public function referenceIcoBy(Profile $profile) :self
    {
        $this->referencerIco = $profile;
        return $this;
    }

    public function getReferencer() :?Profile
    {
        return $this->referencer;
    }

    public function getIcoReferencer() :?Profile
    {
        return $this->referencerIco;
    }

    public function getReferrals() :Collection
    {
        return $this->referencedProfiles->filter(function (Profile $profile) {
            if (is_null($profile->user))
                return false;

            return $profile->user->isEnabled();
        });
    }

    public function getReferralCode() :string
    {
        return $this->referralCode;
    }

    /**
     * Returns suggested reward if this profile is referenced
     * by some referral. Otherwise returns zero.
     * @param int $suggestedReward
     * @return int
     */
    public function getRewardOfReferredSite(int $suggestedReward) :int
    {
        return $this->isReferred() ? $suggestedReward : 0;
    }

    /**
     * Returns reward which will go to profile which referenced this profile
     * @return int
     */
    public function calculateOutcomeReferralReward(Crypto $crypto) :int
    {
        $reward = $this->getPreserveFor($crypto)->getReferralReward();
        foreach ($this->getSites($crypto) as $site)
            $reward += $site->getReferralReward();

        return $reward;
    }

    /**
     * Returns reward which profile earned by referral program
     * @return int
     */
    public function calculateIncomeReferralReward(Crypto $crypto) :int
    {
        $reward = 0;

        /** @var Profile $referral */
        foreach ($this->getReferrals() as $referral)
            $reward += $referral->calculateOutcomeReferralReward($crypto);

        return $reward;
    }

    /**
     * Returns count of referrals that profile has
     * @return int
     */
    public function getReferralsCount() :int
    {
        return count($this->getReferrals());
    }
    
    public function generateReferralCode() :void
    {
        $this->referralCode = Uuid::uuid4();
    }

    private function getPreserves() :array
    {
        return $this->preserves->toArray();
    }

    private function getPreserveFor(Crypto $crypto) :Preserve
    {
        foreach ($this->getPreserves() as $preserve)
            if ($preserve->getCrypto()->getSymbol() == $crypto->getSymbol())
                return $preserve;

        $newPreserve = new Preserve($this, $crypto);
        $this->preserves->add($newPreserve);
        return $newPreserve;
    }

    private function hasDefaultSite(Crypto $crypto) :bool
    {
        foreach ($this->getSites($crypto) as $site)
            if (!$site->isVisible())
                return true;

        return false;
    }

    private function isReferred() :bool
    {
        return !is_null($this->referencer);
    }

    public function getPendingPayoutReward(Crypto $crypto) :int
    {
        return $this->getTotalReward(Status::PENDING, $crypto);
    }

    public function getPaidPayoutReward(Crypto $crypto) :int
    {
        return $this->getTotalReward(Status::PAID, $crypto);
    }

    public function getWalletImp() : WalletImp
    {
        if (null === $this->walletImp || empty($this->walletImp->getId()))
            $this->walletImp = new WalletImp();
        return $this->walletImp;
    }

    private function getTotalReward(string $status, Crypto $crypto) :int
    {
        return array_sum($this->payments
            ->filter(function(Payment $payment) use ($status, $crypto) {
                return $payment->getStatus() === $status
                    && $payment->getCrypto()->getSymbol() == $crypto->getSymbol();
            })
            ->map(function(Payment $payment) { return $payment->getEffectiveAmount(); })
            ->toArray()
        );
    }
}

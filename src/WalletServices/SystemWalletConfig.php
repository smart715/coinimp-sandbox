<?php

namespace App\WalletServices;

use App\Entity\WalletImp\WalletImp;
use App\Manager\ProfileManagerInterface;
use App\OrmAdapter\OrmAdapterInterface;

class SystemWalletConfig
{
    /** @var bool */
    private $isPresale;

    /** @var int */
    private $totalPresale;

    /** @var int */
    private $totalTokensale;

    /** @var string */
    private $tokensaleProfile;

    /** @var string */
    private $tokensaleCommissionPercentage;

    /** @var float */
    private $depositImpMinAmount;

    /** @var string */
    private $availableImpAmount;

    /** @var ProfileManagerInterface */
    private $profileManager;

    /**@var OrmAdapterInterface */
    private $orm;

    public function __construct(
        bool $isPresale,
        int $totlaPresale,
        int $totalTokensale,
        string $tokensaleProfile,
        string $tokensaleCommissionPercentage,
        float $depositImpMinAmount,
        ProfileManagerInterface $profileManager,
        OrmAdapterInterface $ormAdapter
    ) {
        $this->isPresale = $isPresale;
        $this->totalPresale = $totlaPresale;
        $this->totalTokensale = $totalTokensale;
        $this->tokensaleProfile = $tokensaleProfile;
        $this->tokensaleCommissionPercentage = $tokensaleCommissionPercentage;
        $this->depositImpMinAmount = $depositImpMinAmount;
        $this->profileManager = $profileManager;
        $this->orm = $ormAdapter;
        bcscale(WalletImp::impUnitBase);
    }

    public function getCurrentWalletProfile(): string
    {
        return $this->tokensaleProfile;
    }

    public function getCurrentTotalImp(): int
    {
        if ($this->isPresale)
            return $this->totalPresale;
        return $this->totalTokensale;
    }

    public function getCommissionPercentage(): string
    {
        return $this->tokensaleCommissionPercentage;
    }

    public function getDepositImpMinAmount(): float
    {
        return $this->depositImpMinAmount;
    }

    public function calculateAvailableAmount(): string
    {
        if (!$this->availableImpAmount) {
            $profile = $this->profileManager->findByEmail($this->getCurrentWalletProfile());
            $this->availableImpAmount = bcdiv($profile->getWalletImp()->getAvailableAmount(), bcpow(10, WalletImp::impUnitBase));
        }

        return $this->availableImpAmount;
    }

    public function getSoldImp(): string
    {
        $walletImpRepository = $this->orm->getRepository(WalletImp::class);
        return bcdiv($walletImpRepository->getTotalSoldImp(), bcpow(10, WalletImp::impUnitBase));
    }
}

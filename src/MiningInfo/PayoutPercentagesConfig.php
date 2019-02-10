<?php

namespace App\MiningInfo;

class PayoutPercentagesConfig
{
    /** @var float */
    private $hiddenFee;

    /** @var float */
    private $payoutFee;

    /** @var float */
    private $payoutFeeWithoutAds;

    /** @var float */
    private $referralPercentage;

    /** @var float */
    private $orphanFeePercentage;

    /** @var float */
    private $difficultyFee;

    public function __construct(
        float $hiddenFee,
        float $payoutFee,
        float $payoutFeeWithoutAds,
        float $referralPercentage,
        float $orphanFeePercentage,
        float $difficultyFee
    ) {
        assert($hiddenFee >= 0);
        assert($payoutFee >= 0);
        assert($payoutFeeWithoutAds >= 0);
        assert($referralPercentage > 0);
        assert($orphanFeePercentage >= 0);
        assert($difficultyFee >= 0);

        $this->hiddenFee = $hiddenFee;
        $this->payoutFee = $payoutFee;
        $this->payoutFeeWithoutAds = $payoutFeeWithoutAds;
        $this->referralPercentage = $referralPercentage;
        $this->orphanFeePercentage = $orphanFeePercentage;
        $this->difficultyFee = $difficultyFee;
    }

    public function getHiddenFee(): float
    {
        return $this->hiddenFee;
    }

    public function getPayoutFee(): float
    {
        return $this->payoutFee;
    }

    public function getPayoutFeeWithoutAds(): float
    {
        return $this->payoutFeeWithoutAds;
    }

    public function getReferralPercentage(): float
    {
        return $this->referralPercentage;
    }

    public function getDifficultyFee(): float
    {
        return $this->difficultyFee;
    }

    public function getOrphanFeePercentage(): float
    {
        return $this->orphanFeePercentage;
    }
}

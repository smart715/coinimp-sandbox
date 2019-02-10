<?php

namespace App\Manager;

use App\Communications\ApiCurrencyConverter;
use App\Currency\CurrencyInfo;
use App\Entity\Profile;
use App\Entity\WalletImp\WalletImp;
use App\Response\DepositImpResponseCreator;
use App\WalletServices\SystemWalletConfig;
use App\WalletServices\WalletImpTransactionHandler;

class DepositImpManager implements DepositImpManagerInterface
{
    /** @var Array[] */
    private $impBonusPackages;

    /** @var ApiCurrencyConverter */
    private $currencyConverter;

    /** @var WalletImpTransactionHandler */
    private $transactionHandler;

    /** @var InvestBalanceManager */
    private $investBalanceManager;

    /** @var ProfileManagerInterface */
    private $profileManager;

    /** @var SystemWalletConfig */
    private $walletConfig;

    public function __construct(
        array $depositImpBonusPackages,
        ApiCurrencyConverter $currencyConverter,
        WalletImpTransactionHandler $transactionHandler,
        InvestBalanceManager $investBalanceManager,
        ProfileManagerInterface $profileManager,
        SystemWalletConfig $walletConfig
    ) {
        $this->impBonusPackages = $depositImpBonusPackages;
        $this->currencyConverter = $currencyConverter;
        $this->transactionHandler = $transactionHandler;
        $this->investBalanceManager = $investBalanceManager;
        $this->profileManager = $profileManager;
        $this->walletConfig = $walletConfig;
        bcscale(WalletImp::impUnitBase);
    }

    public function depositImp(Profile $profile, string $currencySymbol, string $impAmount): DepositImpResponseCreator
    {
        $currencyInfo = $this->getCurrencyInfo($profile, $currencySymbol, $impAmount);

        $validateCurrency = $this->validateCurrency($currencyInfo);
        if ($validateCurrency) {
            return $validateCurrency;
        }

        $walletProfileName = $this->walletConfig->getCurrentWalletProfile();
        $systemProfile = $this->profileManager->findByEmail($walletProfileName);

        try {
            $this->moveAmountToUser($systemProfile, $profile, $currencyInfo);
        } catch (\Throwable $error) {
            return new DepositImpResponseCreator(
                'NO_IMP',
                'Not enough IMP, You have to buy less.'
            );
        }

        $this->moveCommissionToReferencer($systemProfile, $profile, $currencyInfo);

        $totalImpAmount = bcdiv($profile->getWalletImp()->getAvailableAmount(), bcpow(10, WalletImp::impUnitBase));

        $currentImpAmount = $currencyInfo->getPriceInImp();

        return new DepositImpResponseCreator(
            'SUCCESS',
            "Congrats! you own $totalImpAmount IMP",
            false,
            $this->formatAmount(bcadd($currentImpAmount, $this->getImpBonus($currentImpAmount))),
            $this->formatAmount($totalImpAmount),
            $currencyInfo->getRate()
        );
    }

    private function validateCurrency(CurrencyInfo $currencyInfo): ?DepositImpResponseCreator
    {
        if (!$currencyInfo->symbolIsValid()) {
            return new DepositImpResponseCreator('INVALID_CURRENCY', 'Invalid currency.');
        }

        if (0 === bccomp($currencyInfo->getAmount(), '0')) {
            return new DepositImpResponseCreator('NO_AMOUNT', 'No amount to buy.');
        }

        if (bccomp($currencyInfo->getRealBalance(), $currencyInfo->getAmount()) < 0) {
            //sometimes currency rate changes during processing buy request or precision problem happens in last digit
            // (that sometimes appears when the user try to buy with all his currency balance)
            if (bccomp($currencyInfo->getRealBalance(), 0) > 0) {
                $currencyInfo->setAmount($currencyInfo->getRealBalance());
                return null;
            }

            return new DepositImpResponseCreator(
                'NO_BALANCE',
                'Not enough balance. You only have ' .
                $currencyInfo->getRealBalance() .
                ' ' . $currencyInfo->getSymbol() . '.'
            );
        }

        if (0 === bccomp($currencyInfo->getRate(), '0')) {
            return new DepositImpResponseCreator('NO_PRICE', 'Getting currency price failed.');
        }

        $depositImpMinAmount = $this->walletConfig->getDepositImpMinAmount();
        if (bccomp($currencyInfo->getPriceInImp(), $depositImpMinAmount) < 0) {
            return new DepositImpResponseCreator('LOW_AMOUNT', "You can not buy less than $depositImpMinAmount");
        }

        $depositImpMaxAmount = $this->walletConfig->calculateAvailableAmount();
        $impAmount = $currencyInfo->getPriceInImp();
        if (bccomp(bcadd($impAmount, $this->getImpBonus($impAmount)), $depositImpMaxAmount) > 0) {
            return new DepositImpResponseCreator('HIGH_AMOUNT', "Maximum IMP you can buy, is $depositImpMaxAmount");
        }

        return null;
    }

    private function getCurrencyInfo(Profile $profile, string $symbol, float $impAmount): CurrencyInfo
    {
        return new CurrencyInfo(
            $profile,
            $symbol,
            $impAmount,
            $this->investBalanceManager,
            $this->currencyConverter
        );
    }

    private function moveAmountToUser(Profile $systemProfile, Profile $profile, CurrencyInfo $currencyInfo): void
    {
        $impAmount = $currencyInfo->getPriceInImp();
        $impBonus = (int)(bcmul($this->getImpBonus($impAmount), bcpow(10, WalletImp::impUnitBase)));
        $impAmountInt = (int)(bcmul($impAmount, bcpow(10, WalletImp::impUnitBase)));

        $this->transactionHandler->moveAmountToUser(
            $systemProfile,
            $profile,
            $impAmountInt,
            $this->walletConfig->getCurrentWalletProfile(),
            "Buy IMP using " . $currencyInfo->getSymbol(),
            [
                'currencySymbol' => $currencyInfo->getSymbol(),
                'currencyAmount' => $currencyInfo->getAmount(),
                'currencyPriceInUsd' => $currencyInfo->getRate(),
                'impPriceInUsd' => $currencyInfo->getImpPriceInUsd(),
            ]
        );
        if ($impBonus > 0) {
            $this->transactionHandler->addToUser(
                $profile,
                $impBonus,
                "Bonus",
                "Bonus in " . $this->walletConfig->getCurrentWalletProfile()
            );
        }
        $this->transactionHandler->addToActualPaid(
            $profile,
            $impAmountInt
        );
    }

    private function moveCommissionToReferencer(Profile $systemProfile, Profile $profile, CurrencyInfo $currencyInfo): void
    {
        $commission = $this->walletConfig->getCommissionPercentage();
        $referencerProfile = $profile->getIcoReferencer();

        $depositImpMaxAmount = $this->walletConfig->calculateAvailableAmount();
        $impCommissionAmount = bcmul($currencyInfo->getPriceInImp(), $commission);

        if ($commission && $referencerProfile && ($impCommissionAmount < $depositImpMaxAmount)) {
            $this->transactionHandler->addToUser(
                $referencerProfile,
                (int)(bcmul($impCommissionAmount, bcpow(10, WalletImp::impUnitBase))),
                'ico_referral',
                'Add ICO referral commission to user wallet',
                [
                    'referrerProfileId' => $profile->getId(),
                ]
            );
        }
    }

    private function getImpBonus(string $impAmount): string
    {
        $bonusPackage = $this->getBonusPackageOfImp($impAmount);

        return $bonusPackage['bonusValue'] ?? '0';
    }

    private function getBonusPackageOfImp(string $impAmount): array
    {
        $selectedPackage = [];
        foreach ($this->impBonusPackages as $package) {
            $selectedPackageAmount = $selectedPackage['amount'] ?? 0;
            if (bccomp($impAmount, $package['amount']) >= 0 && $package['amount'] > $selectedPackageAmount) {
                $selectedPackage = $package;
            }
        }

        return $selectedPackage;
    }

    private function formatAmount(string $amount): string
    {
        return number_format($amount, 8, '.', '');
    }
}

<?php

namespace App\Tests\Entity;

use App\Entity\Crypto;
use App\Entity\Payment;
use App\Entity\Payment\Status;
use App\Entity\Profile;
use App\Entity\Site;
use App\Entity\User;
use App\Entity\Preserve;
use Doctrine\Common\Collections\ArrayCollection;
use PHPUnit\Framework\TestCase;
use ReflectionClass;

class ProfileTest extends TestCase
{
    public function testGetRewardOfReferredSiteReturnsZeroIfProfileWasNotReferred() :void
    {
        $profile = new Profile;

        $this->assertEquals(0, $profile->getRewardOfReferredSite(100));
    }

    public function testGetRewardOfReferredSiteReturnsSuggestedAmountIfProfileIsReferred() :void
    {
        $referencer = new Profile;
        $referencee = new Profile;
        $referencee->referenceBy($referencer);

        $this->assertEquals(100, $referencee->getRewardOfReferredSite(100));
    }

    public function testCalculateIncomeReferralRewardOfProfileWithReferrals() :void
    {
        $referrer = new Profile;
        $referenceeA = $this->createProfileWithReferralSites([100, 250]);
        $referenceeB = $this->createProfileWithReferralSites([25, 68, 12]);
        $referenceeA->referenceBy($referrer);
        $referenceeB->referenceBy($referrer);

        $referencedProfiles = $this->getProtectedField($referrer, 'referencedProfiles');
        $referencedProfiles->add($referenceeA);
        $referencedProfiles->add($referenceeB);

        $this->assertEquals(
            100 + 250 + 25 + 68 + 12,
            $referrer->calculateIncomeReferralReward($this->createCryptoMock('xmr'))
        );
    }

    public function testCalculateIncomeReferralRewardOfProfileWithoutReferrals() :void
    {
        $referrer = new Profile;
        $this->assertEquals(
            0,
            $referrer->calculateIncomeReferralReward($this->createCryptoMock('xmr'))
        );
    }

    public function testGetReferralsCount() :void
    {
        $mainProfile = new Profile;
        $this->assertEquals(0, $mainProfile->getReferralsCount());
        $newSignUpA = $newSignUpB = $newSignUpC = $this->createProfile();
        $mainProfileReferrals = $this->getProtectedField($mainProfile, 'referencedProfiles');
        $mainProfileReferrals->add($newSignUpA);
        $mainProfileReferrals->add($newSignUpB);
        $mainProfileReferrals->add($newSignUpC);
        $this->assertEquals(3, $mainProfile->getReferralsCount());
    }

    public function testGetReferralsCountIfSomeProfilesDoNotHaveUser() :void
    {
        $referrer = $this->createProfile();

        $profileWithUser = $this->createProfile();
        $profileWithoutUser = new Profile;

        $mainProfileReferrals = $this->getProtectedField($referrer, 'referencedProfiles');
        $mainProfileReferrals->add($profileWithUser);
        $mainProfileReferrals->add($profileWithoutUser);

        $this->assertEquals(1, $referrer->getReferralsCount());
    }

    public function testCalculateOutcomeReferralRewardOfProfileWithSites() :void
    {
        $referencee = $this->createProfileWithReferralSites([50, 120]);
        $this->assertEquals(50 + 120, $referencee->calculateOutcomeReferralReward($this->createCryptoMock('xmr')));
    }

    public function testCalculateOutcomeReferralRewardOfProfileWithSitesAndSomePreservedReward() :void
    {
        $referencee = $this->createProfileWithReferralSites([50, 120], 10);
        $this->assertEquals(50 + 120 + 10, $referencee->calculateOutcomeReferralReward($this->createCryptoMock('xmr')));
    }

    public function testCalculateOutcomeReferralRewardOfProfileWithoutSites() :void
    {
        $referencee = $this->createProfileWithReferralSites([]);
        $this->assertEquals(0, $referencee->calculateOutcomeReferralReward($this->createCryptoMock('xmr')));
    }

    /**
     * @dataProvider paymentsProvider
     * @param array $payments
     * @param int $totalPendingAmount
     * @param int $totalPaidAmount
     */
    public function testGetTotalReward(array $payments, int $totalPendingAmount, int $totalPaidAmount) :void
    {
        $profile = $this->createProfileWithPayments($payments);
        $this->assertEquals($totalPendingAmount, $profile->getPendingPayoutReward($this->createCryptoMock('xmr')));
        $this->assertEquals($totalPaidAmount, $profile->getPaidPayoutReward($this->createCryptoMock('xmr')));
    }

    public function testGetReferralSites()
    {
        $siteA = $siteB = $siteC = $siteD = $this->createSiteMockWithCrypto($this->createCryptoMock('xmr'));
        $referralA = $this->createProfileWithSitesAndReferrals([$siteA]);
        $referralB = $this->createProfileWithSitesAndReferrals([$siteB, $siteC]);
        $profile = $this->createProfileWithSitesAndReferrals([$siteD], [$referralA, $referralB]);

        $this->assertEquals([$siteA, $siteB, $siteC], $profile->getReferralSites($this->createCryptoMock('xmr')));
    }

    private function createProfileWithSitesAndReferrals(array $sites, array $referrals = []) :Profile
    {
        $profile = new Profile;
        $reflection = new ReflectionClass($profile);

        $user = $reflection->getProperty('user');
        $user->setAccessible(true);
        $user->setValue($profile, $this->createEnabledUser());

        $profileSites = $this->getProtectedField($profile, 'sites');
        foreach ($sites as $site)
            $profileSites->add($site);

        $profileReferencedProfiles = $this->getProtectedField($profile, 'referencedProfiles');
        foreach ($referrals as $referral)
            $profileReferencedProfiles->add($referral);

        return $profile;
    }

    private function createProfileWithPayments(array $payments) :Profile
    {
        $profile = new Profile;
        $reflection = new ReflectionClass($profile);
        $mockedPayments = [];

        foreach($payments as $payment)
            $mockedPayments[] = $this->mockPayment($payment[0], $payment[1], $this->createCryptoMock('xmr'));

        $paymentsList = new ArrayCollection($mockedPayments);
        $paymentsReflection = $reflection->getProperty('payments');
        $paymentsReflection->setAccessible(true);
        $paymentsReflection->setValue($profile, $paymentsList);
        return $profile;
    }

    private function mockPayment(string $status, int $amount, Crypto $crypto) :Payment
    {
        $payment = $this->createMock(Payment::class);
        $payment->method('getStatus')->willReturn($status);
        $payment->method('getEffectiveAmount')->willReturn($amount);
        $payment->method('getCrypto')->willReturn($crypto);
        return $payment;
    }

    public function paymentsProvider() :array
    {
        return [
            [ [ [Status::PAID, 10] , [Status::PAID, 3] , [Status::PENDING, 5]  , [Status::PENDING, 7] , [Status::ERROR, 20] ] , 12 , 13] ,
            [ [ [Status::PAID, 10] ] , 0 , 10] ,
            [ [] , 0 , 0]
        ];
    }

    /**
     * @param mixed $instance
     * @param string $name
     * @return mixed
     * @throws \ReflectionException
     */
    private function getProtectedField($instance, string $name)
    {
        $reflection = new ReflectionClass($instance);
        $prop = $reflection->getProperty($name);
        $prop->setAccessible(true);
        return $prop->getValue($instance);
    }

    private function createProfile() :Profile
    {
        return $this->createProfileWithReferralSites([]);
    }

    private function createProfileWithReferralSites(
        array $rewardsList, int $preservedReward = 0) :Profile
    {
        $profile = new Profile;

        $sites = $this->getProtectedField($profile, 'sites');
        $reflection = new ReflectionClass($profile);

        $preserve = new Preserve($profile, $this->createCryptoMock('xmr'));
        $preserve->addReferralReward($preservedReward);

        $preserves = $this->getProtectedField($profile, 'preserves');
        $preserves->add($preserve);

        $user = $reflection->getProperty('user');
        $user->setAccessible(true);
        $user->setValue($profile, $this->createEnabledUser());

        foreach ($rewardsList as $reward)
            $sites->add($this->createReferralSite($reward, $this->createCryptoMock('xmr')));

        return $profile;
    }

    /**
     * @return User
     */
    private function createEnabledUser(): object
    {
        $user = $this->createMock(User::class);
        $user->method('isEnabled')->willReturn(true);
        return $user;
    }

    /**
     * @param int $referralReward
     * @param Crypto $crypto
     * @return Site
     */
    private function createReferralSite(int $referralReward, Crypto $crypto): object
    {
        $site = $this->createMock(Site::class);
        $site->method('getReferralReward')->willReturn($referralReward);
        $site->method('getCrypto')->willReturn($crypto);
        return $site;
    }

    private function createCryptoMock(string $crypto) :Crypto
    {
        $cryptoMock = $this->createMock(Crypto::class);
        $cryptoMock->method('getSymbol')->willReturn($crypto);
        return $cryptoMock;
    }

    private function createSiteMockWithCrypto(Crypto $crypto) :Site
    {
        $site = $this->createMock(Site::class);
        $site->method('getCrypto')->willReturn($crypto);
        return $site;
    }
}

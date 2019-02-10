<?php

namespace App\Manager;

use App\Entity\Profile;
use App\Entity\User;
use App\OrmAdapter\OrmAdapterInterface;
use App\Repository\ProfileRepository;
use App\Repository\UserRepository;
use App\WalletServices\WalletImpTransactionHandler;

class ProfileManager implements ProfileManagerInterface
{
    /** @var ProfileRepository */
    private $profileRepository;

    /** @var UserRepository */
    private $userRepository;

    /** @var CryptoManagerInterface */
    private $cryptoManager;

    /** @var OrmAdapterInterface */
    private $orm;

    public function __construct(OrmAdapterInterface $ormAdapter, CryptoManagerInterface $cryptoManager)
    {
        $this->orm = $ormAdapter;
        $this->cryptoManager = $cryptoManager;
        $this->profileRepository = $this->orm->getRepository(Profile::class);
        $this->userRepository = $this->orm->getRepository(User::class);
    }

    public function createProfile(int $userId): Profile
    {
        return $this->doCreateProfile($userId, function (Profile $profile): void {
        });
    }

    public function createProfileReferral(int $userId, string $referralCode, bool $isIco = false): Profile
    {
        return $this->doCreateProfile(
            $userId,
            function (Profile $profile) use ($referralCode, $isIco): void {
                $referrer = $this->profileRepository->findReferrer($referralCode);

                if (is_null($referrer))
                    return;

                $isIco ? $profile->referenceIcoBy($referrer) : $profile->referenceBy($referrer);
            }
        );
    }

    public function getProfile(int $userId): Profile
    {
        $profile = $this->profileRepository->findByUserId($userId);

        if (is_null($profile))
            return $this->createProfile($userId);

        foreach ($this->cryptoManager->getAll() as $crypto)
            $profile->createDefaultSite($crypto);

        $this->orm->persist($profile);
        $this->orm->flush();

        return $profile;
    }

    public function findByEmail(string $email): ?Profile
    {
        /** @var User|null $user */
        $user = $this->userRepository->findByEmail($email);

        if (is_null($user))
            return null;

        return $this->getProfile($user->getId());
    }

    public function findByUserId(string $userId): ?Profile
    {
        return $this->profileRepository->findByUserId($userId);
    }

    public function deleteProfile(Profile $profile): void
    {
        $this->orm->remove($profile);
        $this->orm->flush();
    }

    public function createReferralCode(Profile $profile): string
    {
        if (empty($profile->getReferralCode())) {
            $profile->generateReferralCode();
            $this->orm->persist($profile);
            $this->orm->flush();
        }

        return $profile->getReferralCode();
    }

    private function doCreateProfile(int $userId, callable $changeProfile): Profile
    {
        /** @var User $user */
        $this->userRepository->clearUserCountCache();
        $user = $this->userRepository->find($userId);
        $profile = Profile::fromNewUser($user);

        foreach ($this->cryptoManager->getAll() as $crypto)
            $profile->createDefaultSite($crypto);

        $changeProfile($profile);
        $this->orm->persist($profile);
        $this->orm->flush();
        return $profile;
    }
}

<?php

namespace App\Manager;

use App\Entity\Profile;
use App\WalletServices\WalletImpTransactionHandler;

interface ProfileManagerInterface
{
    public function createProfile(int $userId): Profile;
    public function createProfileReferral(int $userId, string $referralCode, bool $isIco = false): Profile;
    public function getProfile(int $userId): Profile;
    public function findByEmail(string $email): ?Profile;
    public function findByUserId(string $email): ?Profile;
    public function deleteProfile(Profile $profile): void;
    public function createReferralCode(Profile $profile): string;
}

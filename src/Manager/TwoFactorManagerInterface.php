<?php

namespace App\Manager;

use App\Entity\GoogleAuthenticatorEntry;
use App\Entity\User;
use Symfony\Component\Form\FormInterface;

interface TwoFactorManagerInterface
{
    public function checkCode(User $user, FormInterface $form): bool;
    public function generateUrl(User $user): string;
    public function getGoogleAuthEntry(int $userId): GoogleAuthenticatorEntry;
    public function generateSecretCode(): string;
    public function generateBackupCodes(): array;
}

<?php

namespace App\Manager;

use App\Entity\User;
use FOS\UserBundle\Model\UserInterface;

interface UserManagerInterface
{
    public function findUserByEmail(string $email): ?UserInterface;
    public function updatePassword(User $user): void;
    public function sendEmailConfirmation(User $user): void;
    public function confirmTempEmail(User $user): void;
    public function sendResettingEmail(User $user): void;
}

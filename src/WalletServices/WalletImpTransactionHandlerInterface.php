<?php

namespace App\WalletServices;

use App\Entity\Profile;

interface WalletImpTransactionHandlerInterface
{
    public function addToUser(
        Profile $profile,
        int $amount,
        string $name,
        ?string $description = null,
        array $data = []
    ): void;

    public function subFromUser(
        Profile $profile,
        int $amount,
        string $name,
        ?string $description = null,
        array $data = []
    ): void;

    public function freezeFromUser(
        Profile $profile,
        int $amount,
        string $name,
        ?string $description = null,
        array $data = []
    ): void;

    public function moveFreezeAmountToUser(
        Profile $fromProfile,
        Profile $toProfile,
        int $amount,
        string $name,
        ?string $description = null,
        array $data = []
    ): void;

    public function moveAmountToUser(
        Profile $fromProfile,
        Profile $toProfile,
        int $amount,
        string $name,
        ?string $description = null,
        array $data = []
    ): void;

    public function addToActualPaid(
        Profile $profile,
        int $amount
    ): void;
}

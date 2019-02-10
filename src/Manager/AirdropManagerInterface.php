<?php

namespace App\Manager;

use App\Entity\Profile;
use App\Results\Airdrop\AirdropResult;

interface AirdropManagerInterface
{
    public function addAirdrop(string $code, Profile $profile, Profile $airdrops): AirdropResult;
    public function getAirdropsProfileEmail(): string;
    public function hasAirdrops(Profile $profile): bool;
    public function getAirdropCode(): ?string;
}

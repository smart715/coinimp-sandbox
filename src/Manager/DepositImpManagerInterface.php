<?php

namespace App\Manager;

use App\Entity\Profile;
use App\Response\DepositImpResponseCreator;

interface DepositImpManagerInterface
{
    public function depositImp(Profile $profile, string $currencySymbol, string $impAmount): DepositImpResponseCreator;
}

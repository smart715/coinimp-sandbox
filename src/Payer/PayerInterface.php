<?php

namespace App\Payer;

use App\Entity\Crypto;
use App\Entity\Profile;

interface PayerInterface
{
    public function pay(Profile $profile, Crypto $crypto, string $paymentId, float $quantity = 0): PaymentResult;
}

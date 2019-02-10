<?php

namespace App\Payer;

interface PayoutCallbackHandlerInterface
{
    public function process(array $payload): bool;
}

<?php

namespace App\Payer\Communicator;

use App\Entity\Crypto;
use RuntimeException;

interface CommunicatorInterface
{
    public function delegatePayment(
        int $id,
        int $amount,
        string $recipientAddress,
        string $crypto,
        string $paymentId
    ): void;

    /**
     * @param callable $onPayout Must take an array argument which will contain message
     * payload and return nothing
     */
    public function listenForPayouts(callable $onPayout): void;

    public function fetchBalance(Crypto $crypto): array;

    public function sendRetryMessage(CallbackMessage $message): void;
}

<?php

namespace App\Payer;

class PaymentResult
{
    public const SUCCESS = 1;
    public const EMPTY_WALLET_ADDRESS = 2;
    public const TOO_SMALL_REWARD = 3;
    public const FAILURE = 4;
    public const HAS_PENDING = 5;
    public const WRONG_PAYMENT_ID = 6;
    public const QUANTITY_EXCEED_REWARD = 7;

    /** @var array */
    private const MESSAGES = [
        self::SUCCESS =>
            'Payout has been successfully issued!',

        self::EMPTY_WALLET_ADDRESS =>
            'Please specify your wallet address.',

        self::TOO_SMALL_REWARD =>
            'You have not reached the minimum payout limit yet.',

        self::FAILURE =>
            'We have failed to pay you, please contact us or try again later.',

        self::HAS_PENDING =>
            'You have pending payments, please contact us or try again later.',

        self::WRONG_PAYMENT_ID =>
            'Please enter a valid Payment ID.',

        self::QUANTITY_EXCEED_REWARD =>
            'Please enter a valid Quantity.',
    ];

    /** @var int */
    private $result;

    public function __construct(int $result)
    {
        assert(in_array($result, array_keys(self::MESSAGES)));
        $this->result = $result;
    }

    public function getMessage(): string
    {
        return self::MESSAGES[$this->result];
    }

    public function getFlashType(): string
    {
        return self::SUCCESS === $this->result ? 'success' : 'error';
    }

    public function getResult(): int
    {
        return $this->result;
    }
}

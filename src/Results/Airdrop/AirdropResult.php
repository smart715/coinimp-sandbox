<?php

namespace App\Results\Airdrop;

class AirdropResult
{
    public const SUCCESS = 1;
    public const ALREADY_RECIEVED_AIRDROP = 2;
    public const INVALID_CODE = 3;
    public const FAILURE = 4;

    /** @var string[] */
    private $messages ;

    /** @var int */
    private $result;

    public function __construct(int $result, int $airdropValue = 0)
    {
        $this->messages = [
            self::SUCCESS =>
                'Congratulations! You have received ' . $airdropValue . ' IMP tokens!',

            self::ALREADY_RECIEVED_AIRDROP =>
                'Sorry, you already received IMP airdrops',

            self::INVALID_CODE =>
                'Sorry, this code is invalid or was already redeemed',

            self::FAILURE =>
                'Sorry, failed to assign airdrops for you, please contact us or try again later.',
        ];
        assert(array_key_exists($result, $this->messages));
        $this->result = $result;
    }

    public function getMessage(): string
    {
        return $this->messages[$this->result];
    }

    public function getFlashType(): string
    {
        return self::SUCCESS === $this->result ? 'success' : 'error';
    }
}

<?php

namespace App\Payer\Communicator\Model;

class MoneroExtras implements ExtrasInterface
{
    public const CRYPTO = 'xmr';

    /** @var mixed[] */
    protected $extras;

    public function __construct(array $extras)
    {
        $this->extras = $extras;
    }

    public function getTransactionKey(): string
    {
        return $this->extras['tx_key'];
    }
}

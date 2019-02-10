<?php

namespace App\Payer\Communicator\Factory;

use App\Payer\Communicator\Model\EmptyExtras;
use App\Payer\Communicator\Model\ExtrasInterface;
use App\Payer\Communicator\Model\MoneroExtras;

class ExtrasFactory
{
    /** @var mixed[] */
    private $extras;

    public function __construct(array $extras)
    {
        $this->extras = $extras;
    }

    public function create(string $crypto): ExtrasInterface
    {
        switch ($crypto) {
            case MoneroExtras::CRYPTO:
                return new MoneroExtras($this->extras);
            default:
                return new EmptyExtras();
        }
    }
}

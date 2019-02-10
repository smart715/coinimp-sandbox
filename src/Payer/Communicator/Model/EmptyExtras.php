<?php

namespace App\Payer\Communicator\Model;

class EmptyExtras implements ExtrasInterface
{
    public function getTransactionKey(): string
    {
        return '';
    }
}

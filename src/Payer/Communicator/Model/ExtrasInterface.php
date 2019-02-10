<?php

namespace App\Payer\Communicator\Model;

interface ExtrasInterface
{
    public function getTransactionKey(): string;
}

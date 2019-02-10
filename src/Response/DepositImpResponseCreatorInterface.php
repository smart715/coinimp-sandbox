<?php

namespace App\Response;

interface DepositImpResponseCreatorInterface
{
    public function getCode(): string;

    public function getError(): bool;

    public function getMessage(): string;

    public function getCurrentImpAmount(): float;
    
    public function getTotalImpAmount(): float;

    public function getResponse(): array;
}

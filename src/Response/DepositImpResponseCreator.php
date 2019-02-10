<?php

namespace App\Response;

class DepositImpResponseCreator implements DepositImpResponseCreatorInterface
{
    /** @var string */
    private $code;

    /** @var string */
    private $message;

    /** @var bool */
    private $error;

    /** @var float */
    private $currentImpAmount;

    /** @var float */
    private $totalImpAmount;

    /** @var float */
    private $rate;

    public function __construct(
        string $code,
        string $message,
        bool $error = true,
        float $currentImpAmount = 0,
        float $totalImpAmount = 0,
        float $rate = 0
    ) {
        $this->code = $code;
        $this->message = $message;
        $this->error = $error;
        $this->currentImpAmount = $currentImpAmount;
        $this->totalImpAmount = $totalImpAmount;
        $this->rate = $rate;
    }

    public function getCode(): string
    {
        return $this->code;
    }

    public function getError(): bool
    {
        return $this->error;
    }

    public function getMessage(): string
    {
        return $this->message;
    }

    public function getCurrentImpAmount(): float
    {
        return $this->currentImpAmount;
    }

    public function getTotalImpAmount(): float
    {
        return $this->totalImpAmount;
    }

    public function getRate(): float
    {
        return $this->rate;
    }

    public function getResponse(): array
    {
        return [
            'code' => $this->getCode(),
            'error' => $this->getError(),
            'message' => $this->getMessage(),
            'currentImpAmount' => $this->getCurrentImpAmount(),
            'totalImpAmount' => $this->getTotalImpAmount(),
            'rate' => $this->getRate(),
        ];
    }
}

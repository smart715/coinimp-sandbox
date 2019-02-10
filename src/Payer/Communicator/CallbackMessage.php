<?php

namespace App\Payer\Communicator;

class CallbackMessage
{
    /** @var int */
    private $id;

    /** @var string */
    private $status;

    /** @var string */
    private $transactionHash;

    /** @var mixed[] */
    private $extras;

    /** @var int */
    private $retries;

    /** @var string */
    private $crypto;

    private function __construct(
        int $id,
        string $status,
        string $transactionHash,
        int $retries,
        string $crypto,
        array $extras
    ) {
        $this->id = $id;
        $this->status = $status;
        $this->transactionHash = $transactionHash;
        $this->extras = $extras;
        $this->retries = $retries;
        $this->crypto = $crypto;
    }

    public function getCrypto(): string
    {
        return $this->crypto;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getTransactionHash(): string
    {
        return $this->transactionHash;
    }

    public function getExtras(): array
    {
        return $this->extras;
    }

    public function getRetriesCount(): int
    {
        return $this->retries;
    }

    public static function parse(array $data): self
    {
        return new self(
            $data['id'],
            $data['status'],
            $data['tx_hash'],
            $data['retries'] ?? 0,
            $data['crypto'],
            $data['extras']
        );
    }

    public function getMessageWithIncrementedRetryCount(): self
    {
        return new self(
            $this->id,
            $this->status,
            $this->transactionHash,
            $this->retries + 1,
            $this->crypto,
            $this->extras
        );
    }

    public function toArray(): array
    {
        return [
            'id' => $this->getId(),
            'status' => $this->getStatus(),
            'tx_hash' => $this->getTransactionHash(),
            'retries' => $this->getRetriesCount(),
            'crypto' => $this->getCrypto(),
            'extras' => $this->getExtras(),
        ];
    }
}

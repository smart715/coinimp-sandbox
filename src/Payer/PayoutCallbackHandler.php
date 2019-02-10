<?php

namespace App\Payer;

use App\Entity\Payment;
use App\Entity\Payment\Transaction;
use App\Payer\Communicator\CallbackMessage;
use App\Payer\Communicator\CommunicatorInterface;
use App\Payer\Communicator\Factory\ExtrasFactory;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManagerInterface;
use InvalidArgumentException;
use Psr\Log\LoggerInterface;

class PayoutCallbackHandler implements PayoutCallbackHandlerInterface
{
    private const SUCCESS = true;
    private const FAIL = false;

    /** @var EntityManagerInterface */
    private $orm;

    /** @var CommunicatorInterface */
    private $communicator;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(
        EntityManagerInterface $orm,
        CommunicatorInterface $communicator,
        LoggerInterface $logger
    ) {
        $this->orm = $orm;
        $this->communicator = $communicator;
        $this->logger = $logger;
    }

    public function process(array $payload): bool
    {
        try {
            $message = $this->parseAndValidateMessage($payload);
        } catch (InvalidArgumentException $e) {
            $this->logger->error($e->getMessage());
            return self::FAIL;
        }

        $result = $this->saveTransaction($message);
        return $result;
    }

    private function parseAndValidateMessage(array $payload): CallbackMessage
    {
        foreach (['id', 'status', 'tx_hash', 'crypto', 'extras'] as $requiredField)
            if (!isset($payload[$requiredField]))
                throw new InvalidArgumentException(
                    'Invalid message format: "'.$requiredField.'" is not set'
                );

        return CallbackMessage::parse($payload);
    }

    private function saveTransaction(CallbackMessage $message): bool
    {
        try {
            $this->setStatus($message);
        } catch (DBALException $e) {
            $this->communicator->sendRetryMessage($message->getMessageWithIncrementedRetryCount());
            $this->logger->error($e->getMessage());
            return self::FAIL;
        }

        $this->logger->info('Payment status changed to '.
            ('ok' === $message->getStatus() ? '"paid"' : '"error"').
            ', ID: '.$message->getId());
        return self::SUCCESS;
    }

    private function setStatus(CallbackMessage $message): void
    {
        $this->checkDbConnection();
        if ('ok' !== $message->getStatus()) {
            $this->setErrorStatus($message->getId());
        } else {
            $extras = new ExtrasFactory($message->getExtras());
            $this->setPaidStatus(
                $message->getId(),
                $message->getTransactionHash(),
                $extras->create($message->getCrypto())->getTransactionKey()
            );
        }
    }

    private function checkDbConnection(): void
    {
        $conn = $this->orm->getConnection();
        if (false === $conn->ping()) {
            $conn->close();
            $conn->connect();
        }
    }

    private function setErrorStatus(int $id): void
    {
        /** @var Payment|null $payment */
        $payment = $this->orm->find(Payment::class, $id);
        if (is_null($payment))
            return;

        $payment->setStatus(Payment\Status::ERROR);
        $this->orm->flush();
    }

    private function setPaidStatus(int $id, string $hash, string $key): void
    {
        /** @var Payment|null $payment */
        $payment = $this->orm->find(Payment::class, $id);
        if (is_null($payment))
            return;

        $payment->setStatus(Payment\Status::PAID);
        $transaction = new Transaction($hash, $key);
        $this->orm->persist($transaction);
        $payment->setTransaction($transaction);
        $this->orm->flush();
    }
}

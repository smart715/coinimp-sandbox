<?php

namespace App\Payer\Communicator;

use App\Entity\Crypto;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Message\AMQPMessage;
use Psr\Log\LoggerInterface;
use RuntimeException;

class RabbitmqCommunicator implements CommunicatorInterface
{
    private const QUEUE_NAME = 'payment';
    private const CALLBACK_QUEUE_NAME = 'payment-callback';
    private const CALLBACK_EXCHANGE_NAME = 'payment-callback-exchange';
    private const BALANCE_QUEUE_NAME = 'payment-balance';
    private const BALANCE_CALLBACK_QUEUE_NAME = 'payment-balance-callback';
    private const RETRY_CALLBACK_QUEUE_NAME = 'payment-callback-retry';
    private const RETRY_CALLBACK_EXCHANGE_NAME = 'payment-callback-retry-exchange';
    private const IS_PASSIVE = false;
    private const IS_DURABLE = false;
    private const IS_EXLUSIVE = false;
    private const IS_AUTO_DELETED = false;
    private const IS_NO_LOCAL = false;
    private const IS_NO_ACK = false;
    private const IS_NO_WAIT = false;
    private const LONG_STR = 'S';

    /** @var RabbitmqConfig */
    private $config;

    /** @var null|string */
    private $response = null;

    /** @var int|string */
    private $correlationId = 0;

    /** @var LoggerInterface */
    private $logger;

    public function __construct(RabbitmqConfig $config, LoggerInterface $logger)
    {
        $this->config = $config;
        $this->logger = $logger;
    }

    public function delegatePayment(
        int $id,
        int $amount,
        string $recipientAddress,
        string $crypto,
        string $paymentId
    ): void {
        $connection = $this->config->createStreamConnection();
        $channel = $connection->channel();

        $this->publishMessage($channel, $this->createMessage(
            $id,
            $amount,
            $recipientAddress,
            $crypto,
            $paymentId
        ));

        $channel->close();
        $connection->close();
    }

    public function listenForPayouts(callable $onPayout): void
    {
        $channel = $this->createChannel();
        $channel->basic_consume(
            self::CALLBACK_QUEUE_NAME,
            '',
            self::IS_NO_LOCAL,
            self::IS_NO_ACK,
            self::IS_EXLUSIVE,
            self::IS_NO_WAIT,
            function (AMQPMessage $message) use ($onPayout, $channel): void {
                $channel->basic_ack($message->delivery_info['delivery_tag']);
                $onPayout(json_decode($message->body, true));
            }
        );
        while (count($channel->callbacks) > 0) {
            $channel->wait();
        }
    }

    /**
     * @return array
     * @throws RuntimeException
     */
    public function fetchBalance(Crypto $crypto): array
    {
        $this->correlationId = uniqid();
        $this->response = null;

        $channel = $this->config->createStreamConnection()->channel();
        [$callbackQueue, ,] = $this->declareQueue($channel, self::BALANCE_CALLBACK_QUEUE_NAME);
        $this->declareQueue($channel, self::BALANCE_QUEUE_NAME);

        $channel->basic_consume(
            $callbackQueue,
            '',
            false,
            false,
            false,
            false,
            function (AMQPMessage $message) use ($channel): void {
                $channel->basic_ack($message->delivery_info['delivery_tag']);
                $this->onFetchBalance($message);
            }
        );

        $packetMessage = [
            'method' => 'get-balance',
            'crypto' => $crypto->getSymbol(),
        ];
        $message = new AMQPMessage(json_encode($packetMessage), [
            'correlation_id' => $this->correlationId,
            'reply_to' => $callbackQueue,
        ]);

        $channel->basic_publish($message, '', self::BALANCE_QUEUE_NAME);
        while (!$this->response) {
            $channel->wait();
        }

        $responseArray = json_decode($this->response, true);
        if (JSON_ERROR_NONE !== json_last_error())
            throw new RuntimeException('Invalid coinimp-payment response. '.json_last_error_msg());

        return $responseArray;
    }

    public function sendRetryMessage(CallbackMessage $message): void
    {
        $connection = $this->config->createStreamConnection();
        $channel = $connection->channel();
        $channel->basic_publish(
            $this->createRetryMessage($message->toArray(), $message->getRetriesCount()),
            '',
            self::RETRY_CALLBACK_QUEUE_NAME
        );
        $channel->close();
        $connection->close();

        $this->logger->info('Retrying saving transaction with '
            .round($this->calculateRetryDelay($message->getRetriesCount()) / 1000)
            .'s delay: '
            .json_encode($message->toArray()));
    }

    private function createChannel(): AMQPChannel
    {
        $channel = $this->config->createStreamConnection()->channel();

        $this->declareQueue($channel, self::CALLBACK_QUEUE_NAME);
        $channel->exchange_declare(self::CALLBACK_EXCHANGE_NAME, 'fanout');
        $channel->queue_bind(self::CALLBACK_QUEUE_NAME, self::CALLBACK_EXCHANGE_NAME);

        $this->declareRetryQueue($channel);

        return $channel;
    }

    private function declareQueue(AMQPChannel $channel, string $QUEUE_NAME): array
    {
        return $channel->queue_declare(
            $QUEUE_NAME,
            self::IS_PASSIVE,
            self::IS_DURABLE,
            self::IS_EXLUSIVE,
            self::IS_AUTO_DELETED
        );
    }

    private function declareRetryQueue(AMQPChannel $channel): void
    {
        $channel->queue_declare(
            self::RETRY_CALLBACK_QUEUE_NAME,
            self::IS_PASSIVE,
            self::IS_DURABLE,
            self::IS_EXLUSIVE,
            self::IS_AUTO_DELETED,
            self::IS_NO_WAIT,
            [ 'x-dead-letter-exchange' => [self::LONG_STR, self::CALLBACK_EXCHANGE_NAME ] ]
        );
        $channel->exchange_declare(self::RETRY_CALLBACK_EXCHANGE_NAME, 'fanout');
        $channel->queue_bind(self::RETRY_CALLBACK_QUEUE_NAME, self::RETRY_CALLBACK_EXCHANGE_NAME);
    }

    private function onFetchBalance(AMQPMessage $message): void
    {

        if ($message->get('correlation_id') === $this->correlationId)
            $this->response = $message->body;
    }

    private function publishMessage(AMQPChannel $channel, AMQPMessage $message): void
    {
        $this->declareQueue($channel, self::QUEUE_NAME);
        $channel->basic_publish($message, '', self::QUEUE_NAME);
    }

    private function createMessage(
        int $id,
        int $amount,
        string $recipient,
        string $crypto,
        string $paymentId
    ): AMQPMessage {
        return new AMQPMessage(
            $this->createMessagePayload($id, $amount, $recipient, $crypto, $paymentId),
            $this->createMessageOptions()
        );
    }

    private function createMessagePayload(
        int $id,
        int $amount,
        string $recipient,
        string $crypto,
        string $paymentId
    ): string {
        return json_encode([
            'id' => $id,
            'amount' => $amount,
            'recipient' => $recipient,
            'crypto' => $crypto,
            'paymentId' => $paymentId,
        ]);
    }

    private function createMessageOptions(): array
    {
        return [
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
        ];
    }

    private function createRetryMessage(array $payload, int $retriesCount): AMQPMessage
    {
        return new AMQPMessage(json_encode($payload), [
            // exponential backoff
            'expiration' => $this->calculateRetryDelay($retriesCount),
            'delivery_mode' => AMQPMessage::DELIVERY_MODE_PERSISTENT,
        ]);
    }

    private function calculateRetryDelay(int $retriesCount): int
    {
        return intval(round(30000 * pow($retriesCount, 1.5)));
    }
}

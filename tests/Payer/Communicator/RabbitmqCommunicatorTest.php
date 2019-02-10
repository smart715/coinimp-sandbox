<?php

namespace App\Tests\Payer\Communicator;

use App\Entity\Crypto;
use App\Payer\Communicator\RabbitmqCommunicator;
use App\Payer\Communicator\RabbitmqConfig;
use PhpAmqpLib\Channel\AMQPChannel;
use PhpAmqpLib\Connection\AMQPStreamConnection;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;
use RuntimeException;

class RabbitmqCommunicatorTest extends TestCase
{

    public function testFetchBalanceWithValidResponse(): void
    {
        $rabbitmqConfig = $this->createRabbitmqConfig('{"a":1,"b":2,"c":3,"d":4,"e":5}');
        $crypto = $this->createMock(Crypto::class);
        $crypto->method('getSymbol')->willReturn('xmr');

        $rabbitmqCommunicator = new RabbitmqCommunicator($rabbitmqConfig, $this->createMock(LoggerInterface::class));
        $rabbitmqCommunicator->fetchBalance($crypto);
        $this->assertEquals(
            ['a' => 1, 'b' => 2, 'c' => 3, 'd' => 4, 'e' => 5],
            $rabbitmqCommunicator->fetchBalance($crypto)
        );
    }

    public function testFetchBalanceWithInvalidResponse(): void
    {
        $rabbitmqConfig = $this->createRabbitmqConfig('invalid json');
        $this->expectException(RuntimeException::class);
        $this->expectExceptionMessage('Invalid coinimp-payment response. Syntax error');
        $crypto = $this->createMock(Crypto::class);
        $crypto->method('getSymbol')->willReturn('xmr');
        $rabbitmqCommunicator = new RabbitmqCommunicator($rabbitmqConfig, $this->createMock(LoggerInterface::class));
        $rabbitmqCommunicator->fetchBalance($crypto);
    }

    private function createRabbitmqConfig(string $response): RabbitmqConfig
    {
        $callbackFunction = null;

        $channel = $this->createMock(AMQPChannel::class);
        $channel->method('queue_declare')
            ->willReturn(['']);

        $channel->method('basic_consume')
            ->willReturnCallback(
                function (
                    $queue,
                    $consumer_tag,
                    $no_local,
                    $no_ack,
                    $exclusive,
                    $nowait,
                    $callback
                ) use (&$callbackFunction): void {
                    $callbackFunction = $callback;
                }
            );

        $channel->method('basic_publish')
            ->willReturnCallback(function ($message) use (&$callbackFunction, $response): void {
                $message->setBody($response);
                $message->delivery_info['delivery_tag'] = '';
                call_user_func($callbackFunction, $message);
            });

        $amqpStreemConnection = $this->createMock(AMQPStreamConnection::class);
        $amqpStreemConnection->method('channel')
            ->willReturn($channel);

        $rabbitmqConfig = $this->createMock(RabbitmqConfig::class);
        $rabbitmqConfig->method('createStreamConnection')
            ->willReturn($amqpStreemConnection);

        return $rabbitmqConfig;
    }
}

<?php

namespace App\Tests\Payer;

use App\Entity\Payment;
use App\Entity\Payment\Transaction;
use App\Payer\Communicator\CallbackMessage;
use App\Payer\Communicator\CommunicatorInterface;
use App\Payer\PayoutCallbackHandler;
use Doctrine\DBAL\Connection;
use Doctrine\DBAL\DBALException;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\Matcher\InvokedCount;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class PayoutCallbackHandlerTest extends TestCase
{
    public function processSuccessDataProvider(): array
    {
        return [
            ['ok', $this->once()],
            ['fail', $this->never()],
        ];
    }

    /**
     * @dataProvider processSuccessDataProvider
     */
    public function testProcessSuccess(
        string $status,
        InvokedCount $setTransactionCount
    ): void {
        $payload = [
            'id' => 1,
            'status' => $status,
            'tx_hash' => 'fd2a269d0216a245ff0fb18544d3948e289ec528746da0d41c219c4e5fd9fde9',
            'crypto' => 'xmr',
            'extras' => [
                'tx_key' => 'unknown',
            ],
        ];

        $mockPayment = $this->createMock(Payment::class);
        $mockPayment->expects($this->once())
            ->method('setStatus');
        $mockPayment->expects($setTransactionCount)
            ->method('setTransaction')
            ->with($this->isInstanceOf(Transaction::class));

        $mockOrm = $this->mockOrm();
        $mockOrm->method('find')->willReturn($mockPayment);

        $paymentProcessor = new PayoutCallbackHandler(
            $mockOrm,
            $this->createMock(CommunicatorInterface::class),
            $this->createMock(LoggerInterface::class)
        );

        $this->assertTrue($paymentProcessor->process($payload));
    }

    public function testProcessWrongPayload(): void
    {
        $payload = [
            'id' => 1,
            //no status
            'tx_hash' => 'fd2a269d0216a245ff0fb18544d3948e289ec528746da0d41c219c4e5fd9fde9',
            'crypto' => 'xmr',
            'extras' => [
                'tx_key' => 'unknown',
            ],
        ];

        $paymentProcessor = new PayoutCallbackHandler(
            $this->mockOrm(),
            $this->createMock(CommunicatorInterface::class),
            $this->createMock(LoggerInterface::class)
        );

        $this->assertFalse($paymentProcessor->process($payload));
    }

    public function testProcessConnection(): void
    {
        $payload = [
            'id' => 1,
            'status' => 'ok',
            'tx_hash' => 'fd2a269d0216a245ff0fb18544d3948e289ec528746da0d41c219c4e5fd9fde9',
            'crypto' => 'xmr',
            'extras' => [
                'tx_key' => 'unknown',
            ],
        ];

        $mockOrm = $this->mockOrm();
        $mockOrm->method('find')->will($this->throwException(new DBALException()));

        $paymentProcessor = new PayoutCallbackHandler(
            $mockOrm,
            $this->createMock(CommunicatorInterface::class),
            $this->createMock(LoggerInterface::class)
        );

        $this->assertFalse($paymentProcessor->process($payload));
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|EntityManagerInterface
     */
    private function mockOrm(): object
    {
        $mockOrm = $this->createMock(EntityManagerInterface::class);
        $mockOrm->method('getConnection')->willReturn($this->mockConn());
        return $mockOrm;
    }

    /**
     * @return \PHPUnit\Framework\MockObject\MockObject|Connection
     */
    private function mockConn(): object
    {
        $mockConn = $this->createMock(Connection::class);
        $mockConn->method('ping')->willReturn(true);
        return $mockConn;
    }
}

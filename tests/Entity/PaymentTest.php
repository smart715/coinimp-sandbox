<?php

namespace App\Tests\Entity;

use App\Entity\Crypto;
use App\Entity\Profile;
use App\Entity\Payment;
use App\Entity\Payment\Transaction;
use PHPUnit\Framework\TestCase;
use DateTime;

class PaymentTest extends TestCase
{
    public function testDefaultTransactionDataValues() :void
    {
        $payment = new Payment(
            10,
            5,
            new DateTime,
            $this->createMock(Profile::class),
            'asd',
            $this->createMock(Crypto::class)
        );
        $this->assertEquals('-', $payment->getHash());
        $this->assertEquals('-', $payment->getKey());
        $this->assertEquals('asd', $payment->getWalletAddress());
    }

    public function testSetTransaction() :void
    {
        $address = 'addr';
        $payment = new Payment(
            10,
            5,
            new DateTime,
            $this->createMock(Profile::class),
            $address,
            $this->createMock(Crypto::class)
        );

        $hash = 'hash';
        $key = 'key';
        $transaction = $this->createMock(Transaction::class);
        $transaction->method('getHash')->willReturn($hash);
        $transaction->method('getKey')->willReturn($key);

        $payment->setTransaction($transaction);

        $this->assertEquals($hash, $payment->getHash());
        $this->assertEquals($key, $payment->getKey());
        $this->assertEquals($address, $payment->getWalletAddress());
    }
}

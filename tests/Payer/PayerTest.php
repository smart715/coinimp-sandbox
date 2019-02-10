<?php

namespace App\Tests\Payer;

use App\Entity\Crypto;
use App\Entity\Profile;
use App\Manager\Model\TotalStats;
use App\Manager\SiteManagerInterface;
use App\Payer\Communicator\CommunicatorInterface;
use App\Payer\Config\Config;
use App\Payer\Payer;
use App\Payer\PaymentResult;
use App\Repository\PaymentRepository;
use Doctrine\ORM\EntityManagerInterface;
use PHPUnit\Framework\MockObject\Matcher\InvokedAtLeastOnce;
use PHPUnit\Framework\MockObject\Matcher\InvokedCount;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class PayerTest extends TestCase
{
    private const MINIMAL_PAYOUT = 0.2;

    /**
     * @dataProvider pendingPaymentSaveProvider
     * @param InvokedAtLeastOnce|InvokedCount $flushCount
     * @param float $walletReward
     */
    public function testPendingPaymentIsNotSavedIfUserHasNotEnoughBalanceAndViceVersa(
        $flushCount,
        float $walletReward
    ): void {
        $orm = $this->createMock(EntityManagerInterface::class);

        $profile = $this->createMock(Profile::class);
        $profile->method('getWalletAddress')->willReturn('abcdef1234');

        $totalInfo = $this->createMock(TotalStats::class);
        $totalInfo->method('getPendingReward')->willReturn(
            $walletReward * pow(10, 12)
        );
        $siteManager = $this->createMock(SiteManagerInterface::class);
        $siteManager->method('getTotalInfo')->willReturn($totalInfo);

        $paymentRepository = $this->createMock(PaymentRepository::class);
        $orm->method('getRepository')->willReturn($paymentRepository);

        $payer = new Payer(
            $orm,
            $siteManager,
            $this->createSuccessCommunicator(),
            $this->createMock(LoggerInterface::class)
        );
        $orm->expects($flushCount)->method('flush');
        $payer->pay($profile, $this->mockCrypto(
            'xmr',
            self::MINIMAL_PAYOUT,
            0.1
        ), '', self::MINIMAL_PAYOUT);
    }

    public function pendingPaymentSaveProvider(): array
    {
        return [
            [ $this->never(), self::MINIMAL_PAYOUT / 2 ],
            [ $this->atLeastOnce(), self::MINIMAL_PAYOUT ],
        ];
    }

    /**
     * @dataProvider paymentIdProvider
     */
    public function testPay(string $paymentId, int $paymentResult): void
    {
        $orm = $this->createMock(EntityManagerInterface::class);
        $siteManager = $this->createMock(SiteManagerInterface::class);

        $paymentRepository = $this->createMock(PaymentRepository::class);

        if (PaymentResult::SUCCESS == $paymentResult) {
            $paymentRepository->expects($this->once())->method('clearTotalPaidCache');
        }

        $orm->method('getRepository')->willReturn($paymentRepository);

        $payer = new Payer(
            $orm,
            $siteManager,
            $this->createSuccessCommunicator(),
            $this->createMock(LoggerInterface::class)
        );
        $profile = $this->createMock(Profile::class);
        $profile->method('getWalletAddress')->willReturn('abcdef1234');
        $this->assertEquals(
            $paymentResult,
            $payer->pay($profile, $this->createMock(Crypto::class), $paymentId)->getResult()
        );
    }

    /**
     * @dataProvider quantityProvider
     */
    public function testPayWithAmount(string $quantity, int $paymentResult): void
    {
        $orm = $this->createMock(EntityManagerInterface::class);

        $profile = $this->createMock(Profile::class);
        $profile->method('getWalletAddress')->willReturn('abcdef1234');

        $totalInfo = $this->createMock(TotalStats::class);
        $totalInfo->method('getPendingReward')->willReturn(
            self::MINIMAL_PAYOUT * 4 * pow(10, 12)
        );

        $siteManager = $this->createMock(SiteManagerInterface::class);
        $siteManager->method('getTotalInfo')->willReturn($totalInfo);

        $paymentRepository = $this->createMock(PaymentRepository::class);

        if (PaymentResult::SUCCESS == $paymentResult) {
            $paymentRepository->expects($this->once())->method('clearTotalPaidCache');
        }
        $orm->method('getRepository')->willReturn($paymentRepository);
        
        $payer = new Payer(
            $orm,
            $siteManager,
            $this->createSuccessCommunicator(),
            $this->createMock(LoggerInterface::class)
        );

        $this->assertEquals(
            $paymentResult,
            $payer->pay($profile, $this->mockCrypto(
                'xmr',
                self::MINIMAL_PAYOUT,
                0.1
            ), '', $quantity)->getResult()
        );
    }

    public function paymentIdProvider(): array
    {
        return [
            [ '123', PaymentResult::WRONG_PAYMENT_ID ],
            [ '', PaymentResult::SUCCESS ],
            ['123456789a123456789b123456789c123456789d123456789e123456789fABCD', PaymentResult::SUCCESS],
        ];
    }

    public function quantityProvider(): array
    {
        return [
            [ 0, PaymentResult::TOO_SMALL_REWARD ],
            [ self::MINIMAL_PAYOUT / 2, PaymentResult::TOO_SMALL_REWARD ],
            [ self::MINIMAL_PAYOUT, PaymentResult::SUCCESS ],
            [ self::MINIMAL_PAYOUT * 2, PaymentResult::SUCCESS ],
            [ self::MINIMAL_PAYOUT * 6, PaymentResult::QUANTITY_EXCEED_REWARD ],
        ];
    }

    private function createSuccessCommunicator(): CommunicatorInterface
    {
        /** @var CommunicatorInterface $wallet */
        return $this->createMock(CommunicatorInterface::class);
    }

    private function mockCrypto(string $symbol, float $minPayout, float $fee): Crypto
    {
        $crypto = $this->createMock(Crypto::class);
        $crypto->method('getSymbol')->willReturn($symbol);
        $crypto->method('getMinimalPayout')->willReturn($minPayout);
        $crypto->method('getFee')->willReturn($fee);
        return $crypto;
    }
}

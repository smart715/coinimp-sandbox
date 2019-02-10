<?php

namespace App\Payer;

use App\Entity\Crypto;
use App\Entity\Payment;
use App\Entity\Payment\Status;
use App\Entity\Profile;
use App\Manager\SiteManagerInterface;
use App\Payer\Communicator\CommunicatorInterface;
use App\Repository\CryptoRepository;
use App\Repository\PaymentRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Psr\Log\LoggerInterface;

class Payer implements PayerInterface
{
    /** @var EntityManagerInterface */
    private $orm;

    /** @var CommunicatorInterface */
    private $communicator;

    /** @var SiteManagerInterface */
    private $siteManager;

    /** @var LoggerInterface */
    private $logger;

    /** @var Profile $profile */
    private $profile;

    public function __construct(
        EntityManagerInterface $orm,
        SiteManagerInterface $siteManager,
        CommunicatorInterface $communicator,
        LoggerInterface $logger
    ) {
        $this->orm = $orm;
        $this->siteManager = $siteManager;
        $this->communicator = $communicator;
        $this->logger = $logger;
    }

    public function pay(Profile $profile, Crypto $crypto, string $paymentId = '', float $quantity = 0): PaymentResult
    {
        if (!empty($paymentId) && !preg_match('/^[0-9A-Fa-f]{64}$/', $paymentId))
            return new PaymentResult(PaymentResult::WRONG_PAYMENT_ID);

        if ($profile->hasPendingPayments($crypto))
            return new PaymentResult(PaymentResult::HAS_PENDING);

        $this->setProfile($profile);
        $payment = $this->createPayment($crypto, $paymentId, $quantity);

        if ($payment->getAmount() < $crypto->getMinimalPayout() * (10**12))
            return new PaymentResult(PaymentResult::TOO_SMALL_REWARD);

        if ($payment->getAmount() > $this->calculatePendingAmount($crypto))
            return new PaymentResult(PaymentResult::QUANTITY_EXCEED_REWARD);

        if (empty($this->profile->getWalletAddress($crypto)))
            return new PaymentResult(PaymentResult::EMPTY_WALLET_ADDRESS);

        if (!$this->doPay($payment))
            return new PaymentResult(PaymentResult::FAILURE);

        $this->clearTotalPaidCache($payment->getCrypto()->getId());

        return new PaymentResult(PaymentResult::SUCCESS);
    }

    private function clearTotalPaidCache(string $symbolId): void
    {
        /** @var PaymentRepository $paymentRepository */
        $paymentRepository = $this->orm->getRepository(Payment::class);

        $paymentRepository->clearTotalPaidCache($symbolId);
    }

    private function createPayment(Crypto $crypto, string $paymentId = '', float $quantity = 0): Payment
    {
        $payment = new Payment(
            $quantity * (10**12),
            $crypto->getFee() * (10**12),
            new DateTime(),
            $this->profile,
            $this->profile->getWalletAddress($crypto),
            $crypto,
            $paymentId
        );
        return $payment;
    }

    private function calculatePendingAmount(Crypto $crypto): int
    {
        return $this->siteManager->getTotalInfo($crypto)->getPendingReward();
    }

    private function doPay(Payment $payment): bool
    {
        $status = $this->transfer($payment)
            ? Status::PENDING
            : Status::ERROR;

        $payment->setStatus($status);
        $this->orm->persist($payment);
        $this->orm->flush();

        return Status::PENDING === $status;
    }

    private function transfer(Payment $payment): bool
    {
        $this->savePaymentAsPending($payment);
        try {
            $this->communicator->delegatePayment(
                $payment->getId(),
                $payment->getPaidAmount(),
                $payment->getWalletAddress(),
                $payment->getCrypto()->getSymbol(),
                $payment->getPaymentId()
            );
        } catch (\Throwable $e) {
            return $this->logFailure('Exception attempting payment transfer: '.$e->getMessage());
        }

        $this->logger->info('Payment initiated for Profile#'.$this->profile->getId());
        return true;
    }

    private function logFailure(string $reason): bool
    {
        $this->logger->error(
            'Failed to pay to Profile#'.$this->profile->getId().': '.
            $reason
        );
        return false;
    }

    private function setProfile(Profile $profile): void
    {
        $this->siteManager->setProfile($profile);
        $this->profile = $profile;
    }

    private function savePaymentAsPending(Payment $payment): void
    {
        $payment->setStatus(Status::PENDING);
        $this->orm->persist($payment);
        $this->orm->flush();
    }
}

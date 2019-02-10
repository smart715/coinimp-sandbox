<?php

namespace App\Tests\Crypto;

use App\Crypto\CryptoFactory;
use App\Crypto\Updater\CryptoUpdaterInterface;
use App\Entity\Crypto;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

class CryptoFactoryTest extends TestCase
{
    public function testCreateSuccessful(): void
    {
        $cryptoFactory = new CryptoFactory(
            $this->getSupportedCurrencies(),
            $this->createMock(CryptoUpdaterInterface::class)
        );

        $this->assertInstanceOf(Crypto::class, $cryptoFactory->create('xmr'));

        $crypto = new Crypto('xmr', 0.2, 0.02, [95, 98, 106], 'https://chainradar.com/xmr/transaction');
        $this->assertEquals($crypto, $cryptoFactory->create('xmr'));
    }

    public function testCreateNonExistentCurrency(): void
    {
        $cryptoFactory = new CryptoFactory(
            $this->getSupportedCurrencies(),
            $this->createMock(CryptoUpdaterInterface::class)
        );

        $this->expectException(InvalidArgumentException::class);
        $cryptoFactory->create('fail');
    }

    private function getSupportedCurrencies(): array
    {
        return [
            'monero' => [
                'symbol' => 'xmr',
                'payout_fee_percentage' => 1,
                'orphan_blocks_percentage' => 2,
                'minimal_payout' => 0.2,
                'mining_reward_fee_percentage' => 2,
                'difficulty_increase_percentage' => 1,
                'payment_fee' => 0.02,
                'daemon' => 'http://localhost:18081',
                'daemon_response_timeout_seconds' => 10,
                'allowed_wallet_lengths' => [95, 98, 106],
                'explorer_url' => 'https://chainradar.com/xmr/transaction',
            ],
            'webchain' => [
                'symbol' => 'web',
                'payout_fee_percentage' => 1,
                'orphan_blocks_percentage' => 2,
                'minimal_payout' => 0.2,
                'mining_reward_fee_percentage' => 2,
                'difficulty_increase_percentage' => 1,
                'payment_fee' => 0.02,
                'daemon' => 'http://localhost:39573',
                'daemon_response_timeout_seconds' => 10,
                'allowed_wallet_lengths' => [42],
                'explorer_url' => 'https://explorer.webchain.network/tx',
            ],
        ];
    }
}

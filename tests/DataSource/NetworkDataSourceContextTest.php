<?php

namespace App\Tests\DataSource;

use App\DataSource\NetworkDataSource\NetworkDataSourceContext;
use App\DataSource\NetworkDataSource\NetworkDataSourceStrategy;
use App\Entity\Crypto;
use PHPUnit\Framework\TestCase;

class NetworkDataSourceContextTest extends TestCase
{
    /**
     * @dataProvider difficultyProvider
     */
    public function testGetNetworkDifficultyReturnsCorrectValue(
        int $expected,
        array $sources,
        Crypto $crypto
    ): void {
        $context = new NetworkDataSourceContext($sources);
        $this->assertEquals($expected, $context->getNetworkDifficulty($crypto));
    }

    public function difficultyProvider(): array
    {
        $firstTestSources = [
            $this->createDataSourceForDifficulty(true, 10),
            $this->createDataSourceForDifficulty(false, 5),
        ];
        $secondTestSources = [
            $this->createDataSourceForDifficulty(false, 10),
            $this->createDataSourceForDifficulty(true, 5),
        ];

        return [
            [10, $firstTestSources, $this->createCryptoMock('web')],
            [5, $secondTestSources, $this->createCryptoMock('xmr')],
        ];
    }

    /**
     * @dataProvider lastBlockRewardProvider
     */
    public function testGetLastBlockRewardReturnsCorrectValue(
        int $expected,
        array $sources,
        Crypto $crypto
    ): void {
        $context = new NetworkDataSourceContext($sources);
        $this->assertEquals($expected, $context->getLastBlockReward($crypto));
    }

    public function lastBlockRewardProvider(): array
    {
        $firstTestSources = [
            $this->createDataSourceForLastBlockReward(true, 10),
            $this->createDataSourceForLastBlockReward(false, 5),
        ];
        $secondTestSources = [
            $this->createDataSourceForLastBlockReward(false, 10),
            $this->createDataSourceForLastBlockReward(true, 5),
        ];

        return [
            [10, $firstTestSources, $this->createCryptoMock('web')],
            [5, $secondTestSources, $this->createCryptoMock('xmr')],
        ];
    }

    private function createDataSourceForDifficulty(bool $isValid, int $difficulty): NetworkDataSourceStrategy
    {
        $source = $this->createMock(NetworkDataSourceStrategy::class);
        $source->method('isValidCrypto')
            ->willReturn($isValid);
        $source->method('getNetworkDifficulty')
            ->willReturn($difficulty);

        return $source;
    }

    private function createDataSourceForLastBlockReward(bool $isValid, int $lastBlockReward): NetworkDataSourceStrategy
    {
        $source = $this->createMock(NetworkDataSourceStrategy::class);
        $source->method('isValidCrypto')
            ->willReturn($isValid);
        $source->method('getLastBlockReward')
            ->willReturn($lastBlockReward);

        return $source;
    }

    private function createCryptoMock(string $crypto): Crypto
    {
        $cryptoMock = $this->createMock(Crypto::class);
        $cryptoMock->method('getSymbol')->willReturn($crypto);
        return $cryptoMock;
    }
}

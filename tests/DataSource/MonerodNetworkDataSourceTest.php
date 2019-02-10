<?php

namespace App\Tests\DataSource;

use App\Communications\JsonRpc;
use App\DataSource\FetchException;
use App\DataSource\NetworkDataSource\MonerodNetworkDataSource;
use App\Entity\Crypto;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class MonerodNetworkDataSourceTest extends TestCase
{
    private const EXAMPLE_DIFFICULTY = 1200000;
    private const EXAMPLE_REWARD = 228;

    private const EXAMPLE_RESPONSE = [ 'block_header' => [
        'depth' => 2,
        'difficulty' => self::EXAMPLE_DIFFICULTY,
        'hash' => 'abcdef12345',
        'height' => 2018,
        'major_version' => 1,
        'minor_version' => 0,
        'nonce' => 1234,
        'orphan_status' => false,
        'prev_hash' => '12345abcdef',
        'reward' => self::EXAMPLE_REWARD,
        'timestamp' => 20180101,
    ] ];

    public function testGetNetworkDifficulty(): void
    {
        $this->assertEquals(
            self::EXAMPLE_DIFFICULTY,
            $this->createDataSource(self::EXAMPLE_RESPONSE)->getNetworkDifficulty()
        );
    }

    public function testGetNetworkDifficultyIfDifficultyNotInResponse(): void
    {
        $response = self::EXAMPLE_RESPONSE;
        unset($response['block_header']['difficulty']);

        $this->expectException(FetchException::class);
        $this->createDataSource($response)->getNetworkDifficulty();
    }

    public function testGetLastBlockReward(): void
    {
        $this->assertEquals(
            self::EXAMPLE_REWARD,
            $this->createDataSource(self::EXAMPLE_RESPONSE)->getLastBlockReward()
        );
    }

    public function testGetLastBlockRewardIfRewardNotInResponse(): void
    {
        $response = self::EXAMPLE_RESPONSE;
        unset($response['block_header']['reward']);

        $this->expectException(FetchException::class);
        $this->createDataSource($response)->getLastBlockReward();
    }

    public function testDataSourceDoesNotAskTwice(): void
    {
        /** @var MockObject|JsonRpc $jsonRpc */
        $jsonRpc = $this->createJsonRpc(self::EXAMPLE_RESPONSE);
        $jsonRpc->expects($this->once())->method('send');

        $dataSource = new MonerodNetworkDataSource($jsonRpc);
        $dataSource->getLastBlockReward();
        $dataSource->getNetworkDifficulty();
        $dataSource->getNetworkDifficulty();
    }

    public function testDataSourceRemembersFailure(): void
    {
        $msg = 'Some error message';

        /** @var MockObject|JsonRpc $jsonRpc */
        $jsonRpc = $this->createJsonRpc(self::EXAMPLE_RESPONSE);
        $jsonRpc->method('send')->willThrowException(new FetchException($msg));
        $jsonRpc->expects($this->once())->method('send');

        $this->expectException(FetchException::class);
        $this->expectExceptionMessageRegExp("/^.+{$msg}/");

        $dataSource = new MonerodNetworkDataSource($jsonRpc);
        $dataSource->getLastBlockReward();
        $dataSource->getNetworkDifficulty();
        $dataSource->getNetworkDifficulty();
    }

    /**
     * @dataProvider cryptoProvider
     */
    public function testIsValidCrypto(bool $expected, Crypto $crypto): void
    {
        $this->assertEquals(
            $expected,
            $this->createDataSource(self::EXAMPLE_RESPONSE)->isValidCrypto($crypto)
        );
    }

    public function cryptoProvider(): array
    {
        return [
            [true, $this->createCryptoMock('xmr')],
            [false, $this->createCryptoMock('web')],
        ];
    }

    private function createDataSource(array $rpcResponse): MonerodNetworkDataSource
    {
        return new MonerodNetworkDataSource($this->createJsonRpc($rpcResponse));
    }

    private function createJsonRpc(array $response): JsonRpc
    {
        $jsonRpc = $this->createMock(JsonRpc::class);
        $jsonRpc->method('send')
            ->with('getlastblockheader', $this->anything())
            ->willReturn($response);

        return $jsonRpc;
    }

    private function createCryptoMock(string $crypto): Crypto
    {
        $cryptoMock = $this->createMock(Crypto::class);
        $cryptoMock->method('getSymbol')->willReturn($crypto);
        return $cryptoMock;
    }
}

<?php

namespace App\Tests\DataSource;

use App\Communications\JsonRpc;
use App\DataSource\FetchException;
use App\DataSource\NetworkDataSource\WebchaindNetworkDataSource;
use App\Entity\Crypto;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;

class WebchaindNetworkDataSourceTest extends TestCase
{
    private const EXAMPLE_DIFFICULTY = 6347157;
    private const EXAMPLE_REWARD = 49.8 * 1e12;

    private const EXAMPLE_RESPONSE = [
        "difficulty" => "0x60d995", // decimal: 6347157
        "extraData" => "0x",
        "gasLimit" => "0x47e7c4",
        "gasUsed" => "0x5208",
        "hash" => "0x7842e699292c65b423a2373b5d0542c0c80249ac17ad7f463a54f672453945d2",
        "logsBloom" => "0x0",
        "miner" => "0x9bb1faef5dbb862af015d83c2c473182fb8fb5da",
        "nonce" => "0xf3040000684c0000",
        "number" => "0x1f50c", //from second era
        "parentHash" => "0xcd67b15dd9e7c52bc353809688a0352397b26cfda501b751bd1c11a5fcb02a7d",
        "receiptsRoot" => "0x13f12eaffc73db984585667e730db2bc72ab9859ec6dac02fd1b83742e515e49",
        "sha3Uncles" => "0x1dcc4de8dec75d7aab85b567b6ccd41ad312451b948a7413f0a142fd40d49347",
        "size" => "0x256",
        "stateRoot" => "0x9c7eef27ff6e50317af8a09ecd8103e8db459b8fb2a208fbe79ccaf2a4e5d5c5",
        "timestamp" => "0x5b0d09a0",
        "totalDifficulty" => "0xd10b8744d6",
        "transactions" => [
            "0x4290f2257c31e4fdd355ccc3b35c519e9e3b834f2b5086fdec735c68bbe3d540",
        ],
        "transactionsRoot" => "0x2fd17b223aa1398c515224539f4043c3c793b6491f2e684ed498a49363e9fb34",
        "uncles" => [],
    ];

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
        unset($response['difficulty']);

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

    public function testGetLastBlockRewardIfBlockNumberNotInResponse(): void
    {
        $response = self::EXAMPLE_RESPONSE;
        unset($response['number']);

        $this->expectException(FetchException::class);
        $this->createDataSource($response)->getLastBlockReward();
    }

    public function testDataSourceDoesNotAskTwice(): void
    {
        /** @var MockObject|JsonRpc $jsonRpc */
        $jsonRpc = $this->createJsonRpc(self::EXAMPLE_RESPONSE);
        $jsonRpc->expects($this->once())->method('send');

        $dataSource = new WebchaindNetworkDataSource($jsonRpc);
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

        $dataSource = new WebchaindNetworkDataSource($jsonRpc);
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
            [true, $this->createCryptoMock('web')],
            [false, $this->createCryptoMock('xmr')],
        ];
    }

    private function createDataSource(array $rpcResponse): WebchaindNetworkDataSource
    {
        return new WebchaindNetworkDataSource($this->createJsonRpc($rpcResponse));
    }

    private function createJsonRpc(array $response): JsonRpc
    {
        $jsonRpc = $this->createMock(JsonRpc::class);
        $jsonRpc->method('send')
            ->with('eth_getBlockByNumber', ['latest', false])
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

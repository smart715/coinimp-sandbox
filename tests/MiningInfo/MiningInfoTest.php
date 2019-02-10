<?php

namespace App\Tests\MiningInfo;

use App\DataSource\NetworkDataSource\NetworkDataSource;
use App\DataSource\SiteDataSource;
use App\Entity\Crypto;
use App\MiningInfo\MiningInfo;
use App\MiningInfo\PayoutPercentagesConfig;
use Craue\ConfigBundle\Util\Config;
use PHPUnit\Framework\TestCase;
use Psr\Log\LoggerInterface;

class MiningInfoTest extends TestCase
{
    /**
     * @param int $realDifficulty
     * @param float $feePercentage
     * @param int $expectedDifficulty
     * @dataProvider difficultyFeeProvider
     */
    public function testGetGlobalPoolDataReturnsDifficultyWithFee(
        int $realDifficulty,
        float $feePercentage,
        int $expectedDifficulty
    ): void {
        $networkSource = $this->createMock(NetworkDataSource::class);
        $networkSource->method('getNetworkDifficulty')->willReturn($realDifficulty);
        $poolSource = $this->createMock(SiteDataSource::class);

        $percentages = new PayoutPercentagesConfig(
            0,
            0,
            0,
            1,
            2, // fees we don't care about
            $feePercentage
        );

        $miningInfo = new MiningInfo(
            $networkSource,
            $poolSource,
            $this->createMock(LoggerInterface::class),
            $this->createMock(Config::class),
            $percentages,
            $this->createCryptoMock('xmr')
        );

        $this->assertEquals(
            $expectedDifficulty,
            $miningInfo->getGlobalPoolData()->getDifficulty()
        );
    }

    public function difficultyFeeProvider(): array
    {
        return [
            [ 200, 5, 210 ], // 200 with 5% fee
            [ 200, 0, 200 ], // 200 with 0% fee
        ];
    }

    private function createCryptoMock(string $crypto): Crypto
    {
        $cryptoMock = $this->createMock(Crypto::class);
        $cryptoMock->method('getSymbol')->willReturn($crypto);
        return $cryptoMock;
    }
}

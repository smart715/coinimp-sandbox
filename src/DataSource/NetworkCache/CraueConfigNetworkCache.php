<?php

namespace App\DataSource\NetworkCache;

use App\Entity\Crypto;
use Craue\ConfigBundle\Util\Config;
use DateTime;

class CraueConfigNetworkCache implements NetworkCache
{
    /** @var Config */
    private $config;

    /** @var Crypto */
    private $crypto;

    public function __construct(Config $config, Crypto $crypto)
    {
        $this->config = $config;
        $this->crypto = $crypto;
    }

    public function getNetworkDifficulty(): int
    {
        return intval($this->config->get($this->getCrypto().'-network-cache-difficulty') ?? 0);
    }

    public function getLastBlockReward(): int
    {
        return intval($this->config->get($this->getCrypto().'-network-cache-lastblockreward') ?? 0);
    }

    public function getTimestamp(): DateTime
    {
        return new DateTime($this->config->get($this->getCrypto().'-network-cache-timestamp') ?? '1980-01-01');
    }

    public function isEmpty(): bool
    {
        return is_null($this->config->get($this->getCrypto().'-network-cache-timestamp'));
    }

    public function update(
        int $networkDifficulty,
        int $lastBlockReward,
        DateTime $timestamp
    ): void {
        $cryptoSymbol = $this->getCrypto();
        $this->config->setMultiple([
            $cryptoSymbol.'-network-cache-difficulty' => $networkDifficulty,
            $cryptoSymbol.'-network-cache-lastblockreward' => $lastBlockReward,
            $cryptoSymbol.'-network-cache-timestamp' => $timestamp->format('Y-m-d H:i:s'),
        ]);
    }

    private function getCrypto(): string
    {
        return $this->crypto->getSymbol();
    }
}

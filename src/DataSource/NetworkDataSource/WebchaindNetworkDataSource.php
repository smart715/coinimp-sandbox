<?php

namespace App\DataSource\NetworkDataSource;

use App\Communications\JsonRpc;
use App\DataSource\FetchException;
use App\Entity\Crypto;

class WebchaindNetworkDataSource implements NetworkDataSourceStrategy
{
    private const CRYPTO = 'web';
    private const GET_LAST_BLOCK_METHOD = 'eth_getBlockByNumber';
    private const BLOCK_AMOUNT_PER_ERA = 100000;
    private const BASE_REWARD = 50 * 1e12;
    private const REDUCTION_FACTOR = 249 / 250;

    /** @var JsonRpc */
    private $jsonRpc;

    /** @var null|mixed[] */
    private $response = null;

    /** @var string */
    private $errorMessage = '';

    public function __construct(JsonRpc $jsonRpc)
    {
        $this->jsonRpc = $jsonRpc;
    }

    public function getNetworkDifficulty(): int
    {
        $response = $this->fetchResponse();

        if (!isset($response['difficulty']))
            throw new FetchException(
                'Failed to fetch network difficulty for '.self::CRYPTO.': '.$this->errorMessage
            );

        $difficulty = hexdec($response['difficulty']);

        return $difficulty;
    }

    public function getLastBlockReward(): int
    {
        $response = $this->fetchResponse();

        if (!isset($response['number']))
            throw new FetchException(
                'Failed to fetch last block reward for '.self::CRYPTO.': '.$this->errorMessage
            );

        $blockNumber = hexdec($response['number']);
        $currentEra = intval($blockNumber / self::BLOCK_AMOUNT_PER_ERA);
        $lastBlockReward = self::BASE_REWARD * self::REDUCTION_FACTOR ** $currentEra;

        return (int)$lastBlockReward;
    }

    private function fetchResponse(): array
    {
        if (!is_null($this->response))
            return $this->response;

        $this->response = $this->tryFetchingResponse();
        return $this->response;
    }

    public function isValidCrypto(Crypto $crypto): bool
    {
        return self::CRYPTO === $crypto->getSymbol();
    }

    private function tryFetchingResponse(): array
    {
        try {
            return $this->jsonRpc->send(self::GET_LAST_BLOCK_METHOD, ['latest', false]);
        } catch (FetchException $e) {
            $this->errorMessage = $e->getMessage();
            return [];
        }
    }
}

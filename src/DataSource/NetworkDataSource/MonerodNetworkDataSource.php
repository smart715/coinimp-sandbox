<?php

namespace App\DataSource\NetworkDataSource;

use App\Communications\JsonRpc;
use App\DataSource\FetchException;
use App\Entity\Crypto;

class MonerodNetworkDataSource implements NetworkDataSourceStrategy
{
    private const CRYPTO = 'xmr';
    private const GET_LAST_BLOCK_METHOD = 'getlastblockheader';
    private const RESPONSE_CONTAINER_KEY = 'block_header';

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

        return $response['difficulty'];
    }

    public function getLastBlockReward(): int
    {
        $response = $this->fetchResponse();

        if (!isset($response['reward']))
            throw new FetchException(
                'Failed to fetch last block reward for '.self::CRYPTO.': '.$this->errorMessage
            );

        return $response['reward'];
    }

    public function isValidCrypto(Crypto $crypto): bool
    {
        return self::CRYPTO === $crypto->getSymbol();
    }

    private function fetchResponse(): array
    {
        if (!is_null($this->response))
            return $this->response;

        $response = $this->tryFetchingResponse();
        $this->response = $response[self::RESPONSE_CONTAINER_KEY] ?? [];
        return $this->response;
    }

    private function tryFetchingResponse(): array
    {
        try {
            return $this->jsonRpc->send(self::GET_LAST_BLOCK_METHOD, []);
        } catch (FetchException $e) {
            $this->errorMessage = $e->getMessage();
            return [];
        }
    }
}

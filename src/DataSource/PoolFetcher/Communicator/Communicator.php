<?php

namespace App\DataSource\PoolFetcher\Communicator;

use App\DataSource\PoolFetcher\Communicator\Tunnel\TunnelInterface;
use App\Factory\CurlFactoryInterface;
use Exception;

class Communicator implements CommunicatorInterface
{
    /** @var CurlFactoryInterface */
    private $curlFactory;

    /** @var TunnelInterface */
    private $tunnel;

    /** @var int */
    private $maxTimeout;

    public function __construct(
        CurlFactoryInterface $curlFactory,
        TunnelInterface $tunnel,
        int $maxTimeout
    ) {
        $this->curlFactory = $curlFactory;
        $this->tunnel = $tunnel;
        $this->maxTimeout = $maxTimeout;
    }

    /**
     * @inheritdoc
     */
    public function getResponse(string $url): array
    {
        $curl = $this->curlFactory->createCurl();
        $this->tunnel->configureCurl($curl);
        $curl->setOpt(CURLOPT_ENCODING, 'gzip,deflate');
        $curl->setOpt(CURLOPT_TIMEOUT, $this->maxTimeout);
        $curl->get($url);

        if ($curl->error)
            throw new Exception(
                'Failed to contact '.$url."\n".
                $curl->errorMessage
            );

        $parsedResponse = $this->tryJsonDecode($curl->rawResponse, $url);

        return $parsedResponse;
    }

    /**
     * @param string|null $response
     * @param string $url
     * @return array
     * @throws Exception
     */
    private function tryJsonDecode(?string $response, string $url): array
    {
        $response = json_decode($response, true);
        if (JSON_ERROR_NONE !== json_last_error())
            throw new Exception(
                'Failed to parse response from '.$url.
                is_string($response) ? "\n$response" : ''
            );

        return $response;
    }
}

<?php

namespace App\Communications;

use App\Factory\HttpClientFactoryInterface;
use GuzzleHttp\Exception\GuzzleException;
use Psr\Http\Message\ResponseInterface;
use Psr\Log\LoggerInterface;

class ApiCurrencyConverter implements CurrencyConverterInterface
{
    /** @var string */
    private $baseUri;

    /** @var int */
    private $timeoutSeconds;

    /** @var HttpClientFactoryInterface */
    private $clientFactory;

    /** @var LoggerInterface */
    private $logger;

    /** @var mixed*/
    private $response;

    public function __construct(
        HttpClientFactoryInterface $clientFactory,
        LoggerInterface $logger,
        string $baseUri = '',
        int $timeoutSeconds = 0
    ) {
        $this->baseUri = $this->removeTrailingSlash($baseUri);
        $this->timeoutSeconds = $timeoutSeconds;
        $this->clientFactory = $clientFactory;
        $this->logger = $logger;
    }

    public function getRate(string $from, string $to): float
    {
        $queryParams = ['query' => ['fsym' => $from, 'tsyms' => $to]];
        $response = $this->sendRequest($queryParams);
        $responseRate = $response[$to] ?? 0.0;
        return (float)($responseRate ?? 0.0);
    }

    private function sendRequest(array $options = [], string $uri = '', string $method = 'GET'): array
    {
        $params = ['base_uri' => $this->baseUri, 'timeout' => $this->timeoutSeconds];
        $client = $this->clientFactory->createClient($params);
        try {
            $this->response = $client->request($method, $uri, $options);
            return $this->parseResponse($this->response);
        } catch (GuzzleException $exception) {
            $this->logger->error($exception->getMessage());
            return [];
        }
    }

    private function parseResponse(ResponseInterface $response): array
    {
        $contents = $response->getBody()->getContents();
        return $this->jsonToArray($contents);
    }

    private function jsonToArray(string $json): array
    {
        $response = json_decode($json, true);
        
        if (JSON_ERROR_NONE !== json_last_error()) {
            $this->logger->error("Malformed currency conversion json from " . $this->baseUri);
            return [];
        }
        return $response;
    }

    private function removeTrailingSlash(string $uri): string
    {
        return rtrim($uri, '/');
    }
}

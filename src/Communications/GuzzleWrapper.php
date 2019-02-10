<?php

namespace App\Communications;

use App\DataSource\FetchException;
use Exception;
use Graze\GuzzleHttp\JsonRpc\Client;
use Graze\GuzzleHttp\JsonRpc\Message\ResponseInterface;
use Ramsey\Uuid\Uuid;

class GuzzleWrapper implements JsonRpc
{
    /** @var Client|null */
    private $client = null;

    /** @var string */
    private $url;

    /** @var int */
    private $timeoutSeconds;

    public function __construct(string $url, int $timeoutSeconds)
    {
        $this->url = $url;
        $this->timeoutSeconds = $timeoutSeconds;
    }

    public function send(string $methodName, array $requestParams): array
    {
        try {
            $this->constructClient();
            $response = $this->sendRequest($methodName, $requestParams);
            return $this->parseResponse($response);
        } catch (\Throwable $e) {
            throw new FetchException($e->getMessage(), $e->getCode());
        }
    }

    private function sendRequest(string $methodName, array $params): ResponseInterface
    {
        return $this->client->send(
            $this->client->request(
                Uuid::uuid4()->toString(),
                $methodName,
                $params
            )
        );
    }

    private function parseResponse(ResponseInterface $response): array
    {
        return $response->getRpcResult() ?? [];
    }

    private function constructClient(): void
    {
        if (!is_null($this->client))
            return;

        $this->client = Client::factory($this->url, [
            'timeout' => $this->timeoutSeconds,
        ]);
    }
}

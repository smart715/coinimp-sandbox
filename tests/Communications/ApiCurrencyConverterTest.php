<?php

namespace App\Tests\Communications;

use App\Communications\ApiCurrencyConverter;
use App\Factory\HttpClientFactoryInterface;
use GuzzleHttp\ClientInterface;
use GuzzleHttp\Exception\TransferException;
use PHPUnit\Framework\TestCase;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamInterface;
use Psr\Log\LoggerInterface;

class ApiCurrencyConverterTest extends TestCase
{
    private const JSON_RESPONSE = '{"USD": 237.41}';
    private const BASE_URI = 'api.example.com/v1';
    private const TIMEOUT_SECONDS = 10;

    public function testGetRateSuccess(): void
    {
        $streamMock = $this->getStreamMock(self::JSON_RESPONSE);
        $clientMock = $this->getClientMock($streamMock);
        $clientFactoryMock = $this->getClientFactoryMock($clientMock);

        $currencyConverter = new ApiCurrencyConverter(
            $clientFactoryMock,
            $this->createMock(LoggerInterface::class),
            self::BASE_URI,
            self::TIMEOUT_SECONDS
        );
        $this->assertEquals(237.41, $currencyConverter->getRate('XMR', 'USD'));
    }

    public function testGetRateWhenInvalidJson(): void
    {
        $streamMock = $this->getStreamMock('');
        $clientMock = $this->getClientMock($streamMock);
        $clientFactoryMock = $this->getClientFactoryMock($clientMock);

        $currencyConverter = new ApiCurrencyConverter(
            $clientFactoryMock,
            $this->createMock(LoggerInterface::class),
            self::BASE_URI,
            self::TIMEOUT_SECONDS
        );
        $this->assertEquals(0.0, $currencyConverter->getRate('XMR', 'USD'));
    }

    public function testGetRateRequestThrowsException(): void
    {
        $clientMock = $this->createMock(ClientInterface::class);
        $clientMock->method('request')->willThrowException(new TransferException());

        $clientFactoryMock = $this->getClientFactoryMock($clientMock);

        $currencyConverter = new ApiCurrencyConverter(
            $clientFactoryMock,
            $this->createMock(LoggerInterface::class),
            self::BASE_URI,
            self::TIMEOUT_SECONDS
        );
        $this->assertEquals(0.0, $currencyConverter->getRate('XMR', 'USD'));
    }

    public function testParametersAreActuallyUsed(): void
    {
        $streamMock = $this->getStreamMock(self::JSON_RESPONSE);
        $clientMock = $this->getClientMock($streamMock);
        $clientMock->expects($this->once())
            ->method('request')
            ->with(
                $this->equalTo('GET'),
                $this->equalTo(''),
                $this->equalTo(['query' => ['fsym' => 'XMR', 'tsyms' => 'USD']])
            );

        $clientFactoryMock = $this->getClientFactoryMock($clientMock);
        $clientFactoryMock->expects($this->once())
            ->method('createClient')
            ->with($this->equalTo([
                'base_uri' => self::BASE_URI,
                'timeout' => self::TIMEOUT_SECONDS,
            ]));

        $currencyConverter = new ApiCurrencyConverter(
            $clientFactoryMock,
            $this->createMock(LoggerInterface::class),
            self::BASE_URI,
            self::TIMEOUT_SECONDS
        );
        $this->assertEquals(237.41, $currencyConverter->getRate('XMR', 'USD'));
    }

    private function getStreamMock(string $content): StreamInterface
    {
        $streamMock = $this->createMock(StreamInterface::class);
        $streamMock->method('getContents')->willReturn($content);
        return $streamMock;
    }

    /**
     * @param StreamInterface $stream
     * @return \PHPUnit\Framework\MockObject\MockObject|ClientInterface
     */
    private function getClientMock(StreamInterface $stream): object
    {
        $clientMock = $this->createMock(ClientInterface::class);
        $clientMock->method('request')->willReturn($this->getResponseMock($stream));
        return $clientMock;
    }

    /**
     * @param ClientInterface $client
     * @return \PHPUnit\Framework\MockObject\MockObject|HttpClientFactoryInterface
     */
    private function getClientFactoryMock(ClientInterface $client): object
    {
        $clientFactoryMock = $this->createMock(HttpClientFactoryInterface::class);
        $clientFactoryMock->method('createClient')->willReturn($client);
        return $clientFactoryMock;
    }

    /**
     * @param StreamInterface $stream
     * @return \PHPUnit\Framework\MockObject\MockObject|ResponseInterface
     */
    private function getResponseMock(StreamInterface $stream): object
    {
        $responseMock = $this->createMock(ResponseInterface::class);
        $responseMock->method('getBody')->willReturn($stream);
        return $responseMock;
    }
}

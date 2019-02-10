<?php

namespace App\DataSource\PoolFetcher;

use App\DataSource\PoolFetcher\Communicator\CommunicatorInterface;
use App\Entity\Crypto;
use App\MiningInfo\SitePoolData;
use App\MiningInfo\SitePoolDataContainer;
use Exception;

class AggregatorPoolFetcher implements PoolFetcherInterface
{
    /** @var string */
    private $apiUrl;

    /** @var CommunicatorInterface */
    private $communicator;

    public function __construct(
        CommunicatorInterface $communicator,
        string $apiUrl
    ) {
        $this->communicator = $communicator;
        $this->apiUrl = rtrim($apiUrl, '/');
    }

    public function getOneSiteData(string $siteKey, Crypto $crypto): SitePoolDataContainer
    {
        $url = $this->apiUrl.'/site_stats_by_user?key='.$siteKey.'&crypto='.$crypto->getSymbol();
        $response = $this->communicator->getResponse($url);

        if (isset($response['error']) && 'not found' === $response['error'])
            return new SitePoolDataContainer();

        $container = new SitePoolDataContainer();

        foreach ($response['stats'] as $user => $stats)
            $container->set($user, new SitePoolData(
                isset($stats['hashrate'])
                    ? $this->parseHashRate($stats['hashrate'])
                    : 0.0,
                intval($stats['hashes'] ?? 0)
            ));

        return $container;
    }

    private function parseHashRate(string $raw): float
    {
        static $modifiers = [
            'H' => 1,
            'KH' => 1000,
            'MH' => 1000000,
        ];

        [$value, $modifier] = explode(' ', $raw);

        if (!isset($modifiers[$modifier]))
            throw new Exception('Failed to parse hash rate: unknown modifier '.
                "\"{$modifier}\"");

        return floatval($value) * $modifiers[$modifier];
    }
}

<?php

namespace App\DependencyInjection\Compiler;

use App\Communications\CurrentTime;
use App\Communications\GuzzleWrapper;
use App\Crypto\CryptoFactory;
use App\DataSource\NetworkCache\CraueConfigNetworkCache;
use App\DataSource\NetworkDataSource\CachedNetworkDataSource;
use App\DataSource\NetworkDataSource\NetworkDataSource;
use App\DataSource\NetworkDataSource\NetworkDataSourceContext;
use App\Entity\Crypto;
use App\MiningInfo\Factory\MiningInfoFactory;
use App\MiningInfo\MiningInfo;
use App\MiningInfo\PayoutPercentagesConfig;
use Craue\ConfigBundle\Util\Config;
use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Definition;

class ApplicationPass implements CompilerPassInterface
{
    /**
     * @inheritdoc
     */
    public function process(ContainerBuilder $container)
    {
        $supportedCurrencies = $container->getParameter('supported_currencies');
        $miningInfoInstances = [];
        $dataSources = [];

        foreach ($supportedCurrencies as $name => $params) {
            $crypto = $this->getCrypto(
                $params['symbol'],
                $params['minimal_payout'],
                $params['payment_fee'],
                $params['allowed_wallet_lengths'],
                $params['explorer_url']
            );
            $jsonRpc = $this->getJsonRpc($params['daemon'], $params['daemon_response_timeout_seconds']);

            $innerData = $container
                ->getDefinition('network_data_source.'.$name.'d')
                ->setArgument('$jsonRpc', $jsonRpc);

            $dataSources[] = $this->getCachedNetworkDataSource(
                $innerData,
                $container->getDefinition(CurrentTime::class),
                $this->getCache($crypto, $container->getDefinition(Config::class)),
                $container->getParameter('network_data_cache_expiry_minutes')
            );

            $payoutConfig = $this->getPayoutPercentageConfig(
                $params['mining_reward_fee_percentage'],
                $params['payout_fee_percentage'],
                $params['payout_fee_without_ads_percentage'],
                $container->getParameter('referral_percentage'),
                $params['orphan_blocks_percentage'],
                $params['difficulty_increase_percentage']
            );

            $miningInfoInstances[] = $container->autowire('mining_info.'.$name)
                ->setClass(MiningInfo::class)
                ->setArgument('$percentages', $payoutConfig)
                ->setArgument('$crypto', $crypto);
        }

        $container->autowire(CryptoFactory::class)
            ->setClass(CryptoFactory::class)
            ->setArgument('$supportedCurrencies', $supportedCurrencies);

        $container->autowire(MiningInfoFactory::class)
            ->setClass(MiningInfoFactory::class)
            ->setArgument('$miningInfoInstances', $miningInfoInstances);

        $container->autowire(NetworkDataSource::class)
            ->setClass(NetworkDataSourceContext::class)
            ->setArguments([$dataSources]);
    }

    private function getCache(Definition $crypto, Definition $config): Definition
    {
        return new Definition(CraueConfigNetworkCache::class, [
            $config, $crypto,
        ]);
    }

    private function getCachedNetworkDataSource(
        Definition $innerSource,
        Definition $curentTime,
        Definition $cache,
        float $expInterval
    ): Definition {
        $interval = new Definition(
            \DateInterval::class,
            [ '$interval_spec' => 'PT'.$expInterval.'M' ]
        );
        return new Definition(CachedNetworkDataSource::class, [
            '$innerSource' => $innerSource,
            '$currentTime' => $curentTime,
            '$cache' => $cache,
            '$expirationInterval' => $interval,
        ]);
    }

    private function getJsonRpc(string $url, string $timeout): Definition
    {
        return new Definition(GuzzleWrapper::class, [$url, $timeout]);
    }

    private function getCrypto(
        string $name,
        float $minPayout,
        float $fee,
        array $walletLengths,
        string $explorerUrl
    ): Definition {
        return new Definition(
            Crypto::class,
            [$name, $minPayout, $fee, $walletLengths, $explorerUrl]
        );
    }

    private function getPayoutPercentageConfig(
        float $fee,
        float $payout,
        float $payoutWithoutAds,
        float $refPercentage,
        float $orphanFeePercentage,
        float $difficultyFee
    ): Definition {
        return new Definition(PayoutPercentagesConfig::class, [
            '$hiddenFee' => $fee,
            '$payoutFee' => $payout,
            '$payoutFeeWithoutAds' => $payoutWithoutAds,
            '$referralPercentage' => $refPercentage,
            '$orphanFeePercentage' => $orphanFeePercentage,
            '$difficultyFee' => $difficultyFee,
        ]);
    }
}

<?php

namespace App\Crypto;

use App\Crypto\Updater\CryptoUpdaterInterface;
use App\Entity\Crypto;
use InvalidArgumentException;

class CryptoFactory implements CryptoFactoryInterface
{
    /** @var mixed[] */
    private $supportedCurrencies;

    /** @var CryptoUpdaterInterface */
    private $cryptoUpdater;

    public function __construct(array $supportedCurrencies, CryptoUpdaterInterface $cryptoUpdater)
    {
        $this->supportedCurrencies = $supportedCurrencies;
        $this->cryptoUpdater = $cryptoUpdater;
    }

    public function create(string $symbol): Crypto
    {
        $symbol = strtolower($symbol);

        if (!in_array($symbol, $this->getSupportedList()))
            throw new InvalidArgumentException('Invalid cryptocurrency: '.$symbol);

        $minimalPayout = $this->getMinimalPayout($symbol);
        $fee = $this->getFee($symbol);
        $walletLengths = $this->getWalletLengths($symbol);
        $explorerUrl = $this->getExplorerUrl($symbol);

        $crypto = new Crypto($symbol, $minimalPayout, $fee, $walletLengths, $explorerUrl);

        $this->cryptoUpdater->update($crypto);

        return $crypto;
    }

    /**
     * @inheritdoc
     */
    public function getSupportedList(): array
    {
        return array_map(function ($currency) {
            return $currency['symbol'];
        }, $this->supportedCurrencies);
    }

    private function getMinimalPayout(string $symbol): float
    {
        return $this->getParameterValue('minimal_payout', $symbol);
    }

    private function getFee(string $symbol): float
    {
        return $this->getParameterValue('payment_fee', $symbol);
    }

    private function getWalletLengths(string $symbol): array
    {
        return $this->getParameterValue('allowed_wallet_lengths', $symbol);
    }

    private function getExplorerUrl(string $symbol): string
    {
        return $this->getParameterValue('explorer_url', $symbol);
    }

    /** @return mixed */
    private function getParameterValue(string $parameter, string $symbol)
    {
        foreach ($this->supportedCurrencies as $currency)
            if ($currency['symbol'] == $symbol)
                return $currency[$parameter];

        throw new InvalidArgumentException("Parameter '".$parameter."' of '".$symbol."' isn't set");
    }
}

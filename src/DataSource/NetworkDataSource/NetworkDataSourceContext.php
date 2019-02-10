<?php

namespace App\DataSource\NetworkDataSource;

use App\Entity\Crypto;

class NetworkDataSourceContext implements NetworkDataSource
{
    /** @var NetworkDataSourceStrategy[] $networkSources */
    private $networkSources;

    public function __construct(iterable $networkSources)
    {
        $this->networkSources = $networkSources;
    }

    public function getNetworkDifficulty(Crypto $crypto): int
    {
        foreach ($this->networkSources as $source)
            if ($source->isValidCrypto($crypto))
                return $source->getNetworkDifficulty();
    }

    public function getLastBlockReward(Crypto $crypto): int
    {
        foreach ($this->networkSources as $source)
            if ($source->isValidCrypto($crypto))
                return $source->getLastBlockReward();
    }
}

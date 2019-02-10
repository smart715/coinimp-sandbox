<?php

namespace App\MiningInfo;

class SitePoolDataContainer implements \IteratorAggregate
{
    /** @var SitePoolData[]|array */
    private $sitesData;

    /**
     * PoolDataContainer constructor.
     *
     * @param SitePoolData[] $sitesData
     */
    public function __construct(array $sitesData = [])
    {
        $this->sitesData = $sitesData;
    }

    public function set(string $key, SitePoolData $poolData): void
    {
        $this->sitesData[$key] = $poolData;
    }

    public function getAnonymous(): SitePoolData
    {
        return $this->get('anonymous');
    }

    public function get(string $user): SitePoolData
    {
        return $this->sitesData[$user] ?? new SitePoolData(0, 0);
    }

    public function getOverall(): SitePoolData
    {
        $data = [
            'hashrate' => 0.0,
            'hashesTotal' => 0,
        ];

        foreach ($this->sitesData as $siteData) {
            $data['hashrate'] += $siteData->getHashRate();
            $data['hashesTotal'] += $siteData->getHashesTotal();
        }

        return new SitePoolData($data['hashrate'], $data['hashesTotal']);
    }

    public function getUsers(): array
    {
        return array_keys($this->sitesData);
    }

    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->sitesData);
    }
}

<?php

namespace App\MiningInfo\Factory;

use App\Entity\Crypto;
use App\MiningInfo\MiningInfoInterface;

class MiningInfoFactory
{
    /** @var MiningInfoInterface[] $miningInfoInstances */
    private $miningInfoInstances;

    public function __construct(array $miningInfoInstances)
    {
        $this->miningInfoInstances = $miningInfoInstances;
    }

    public function getMiningInfo(Crypto $crypto): MiningInfoInterface
    {
        foreach ($this->miningInfoInstances as $miningInfo)
            if ($miningInfo->getCrypto()->getSymbol() == $crypto->getSymbol())
                return $miningInfo;
    }
}

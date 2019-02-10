<?php

namespace App\DataSource\PoolFetcher\Communicator\Tunnel;

use Curl\Curl;

interface TunnelInterface
{
    public function configureCurl(Curl $curl): void;
}

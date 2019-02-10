<?php

namespace App\DataSource\PoolFetcher\Communicator\Tunnel;

use Curl\Curl;

class NoTunnel implements TunnelInterface
{
    public function configureCurl(Curl $curl): void
    {
    }
}

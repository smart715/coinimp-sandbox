<?php

namespace App\DataSource\PoolFetcher\Communicator\Tunnel;

use Curl\Curl;

class SshTunnel implements TunnelInterface
{
    /** @var string */
    private $tunnelHost;

    /** @var string */
    private $tunnelPort;

    public function __construct(string $tunnelHost, string $tunnelPort)
    {
        $this->tunnelHost = $tunnelHost;
        $this->tunnelPort = $tunnelPort;
    }

    public function configureCurl(Curl $curl): void
    {
        $curl->setOpt(CURLOPT_PROXY, $this->tunnelHost.':'.$this->tunnelPort);
        $curl->setOpt(CURLOPT_PROXYTYPE, CURLPROXY_SOCKS5);
    }
}

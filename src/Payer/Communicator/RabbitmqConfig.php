<?php

namespace App\Payer\Communicator;

use PhpAmqpLib\Connection\AMQPStreamConnection;

class RabbitmqConfig
{
    /** @var string */
    private $host;

    /** @var int */
    private $port;

    /** @var string */
    private $user;

    /** @var string */
    private $password;

    /** @var string */
    private $vhost;

    public function __construct(string $host, int $port, string $user, string $password, string $vhost)
    {
        $this->host = $host;
        $this->port = $port;
        $this->user = $user;
        $this->password = $password;
        $this->vhost = $vhost;
    }

    public function createStreamConnection(): AMQPStreamConnection
    {
        return new AMQPStreamConnection(
            $this->host,
            $this->port,
            $this->user,
            $this->password,
            $this->vhost
        );
    }
}

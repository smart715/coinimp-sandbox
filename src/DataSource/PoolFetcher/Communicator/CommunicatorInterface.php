<?php

namespace App\DataSource\PoolFetcher\Communicator;

use Exception;

interface CommunicatorInterface
{
    /**
     * @param string $url
     * @return array
     * @throws Exception
     */
    public function getResponse(string $url): array;
}

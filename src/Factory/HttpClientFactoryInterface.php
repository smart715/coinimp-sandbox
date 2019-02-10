<?php

namespace App\Factory;

use GuzzleHttp\ClientInterface;

interface HttpClientFactoryInterface
{
    public function createClient(array $parameters): ClientInterface;
}

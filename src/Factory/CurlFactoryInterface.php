<?php

namespace App\Factory;

use Curl\Curl;

interface CurlFactoryInterface
{
    public function createCurl(): Curl;
}

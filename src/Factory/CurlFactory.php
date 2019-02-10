<?php

namespace App\Factory;

use Curl\Curl;

class CurlFactory implements CurlFactoryInterface
{
    public function createCurl(): Curl
    {
        return new Curl();
    }
}

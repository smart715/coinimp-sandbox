<?php

namespace App\Communications;

use App\DataSource\FetchException;

interface JsonRpc
{
    /**
     * @param string $methodName
     * @param array $requestParams
     * @return array Response
     * @throws FetchException
     */
    public function send(string $methodName, array $requestParams): array;
}

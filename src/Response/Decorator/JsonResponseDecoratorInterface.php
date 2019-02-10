<?php

namespace App\Response\Decorator;

use Symfony\Component\HttpFoundation\JsonResponse;

interface JsonResponseDecoratorInterface
{
    /**
     * @param string|array $message
     * @return JsonResponse
     */
    public function failure($message): JsonResponse;

    /**
     * @param string|array $message
     * @return JsonResponse
     */
    public function success($message): JsonResponse;
}

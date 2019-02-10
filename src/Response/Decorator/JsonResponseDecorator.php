<?php

namespace App\Response\Decorator;

use Symfony\Component\HttpFoundation\JsonResponse;

class JsonResponseDecorator implements JsonResponseDecoratorInterface
{
    /** {@inheritdoc} */
    public function failure($message): JsonResponse
    {
        return new JsonResponse(['message' => $message, 'status' => 'failure']);
    }

    /** {@inheritdoc} */
    public function success($message): JsonResponse
    {
        return new JsonResponse(['message' => $message, 'status' => 'success']);
    }
}

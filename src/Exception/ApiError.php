<?php

declare(strict_types=1);

namespace ListInterop\ConvertKit\Exception;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

use function sprintf;

class ApiError extends ResponseError
{
    public static function fromExchange(RequestInterface $request, ResponseInterface $response): self
    {
        return self::withHttpExchange(
            sprintf(
                'The request to "%s" failed with the code %d',
                $request->getUri()->getPath(),
                $response->getStatusCode(),
            ),
            $request,
            $response,
        );
    }
}

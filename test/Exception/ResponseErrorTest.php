<?php

declare(strict_types=1);

namespace Exception;

use Laminas\Diactoros\Request;
use Laminas\Diactoros\Response;
use ListInterop\ConvertKit\Exception\ApiError;
use PHPUnit\Framework\TestCase;

class ResponseErrorTest extends TestCase
{
    public function testThatTheRequestAndResponseCanBeRetrieved(): void
    {
        $request = new Request();
        $response = new Response();

        $error = ApiError::fromExchange($request, $response);

        self::assertSame($request, $error->request());
        self::assertSame($response, $error->response());
    }
}

<?php

declare(strict_types=1);

namespace ListInterop\ConvertKit\Exception;

use ListInterop\ConvertKit\Assert;
use Psr\Http\Client\ClientExceptionInterface as PsrHttpError;
use Psr\Http\Message\RequestInterface;
use RuntimeException;

use function sprintf;

final class RequestFailure extends RuntimeException implements ConvertKitError
{
    private RequestInterface|null $request = null;

    public static function withPsrError(RequestInterface $request, PsrHttpError $error): self
    {
        $instance = new self(sprintf(
            'The request to "%s" failed: %s',
            $request->getUri()->getPath(),
            $error->getMessage(),
        ), 0, $error);

        $instance->request = $request;

        return $instance;
    }

    public function failedRequest(): RequestInterface
    {
        Assert::isInstanceOf(
            $this->request,
            RequestInterface::class,
            'This error was not provided a request instance',
        );

        return $this->request;
    }
}

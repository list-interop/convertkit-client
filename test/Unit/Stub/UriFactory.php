<?php

declare(strict_types=1);

namespace ListInterop\ConvertKit\Test\Unit\Stub;

use Laminas\Diactoros\Uri;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;

final class UriFactory implements UriFactoryInterface
{
    private UriInterface|null $lastUri = null;

    public function createUri(string $uri = ''): UriInterface
    {
        $this->lastUri = new Uri($uri);

        return $this->lastUri;
    }

    public function lastUri(): UriInterface|null
    {
        return $this->lastUri;
    }
}

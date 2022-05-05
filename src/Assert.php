<?php

declare(strict_types=1);

namespace ListInterop\ConvertKit;

use ListInterop\ConvertKit\Exception\AssertionFailed;
use Webmozart\Assert\Assert as WebMozartAssert;

final class Assert extends WebMozartAssert
{
    /**
     * @param string $message
     *
     * @throws AssertionFailed
     *
     * @psalm-pure
     */
    protected static function reportInvalidArgument($message): void // phpcs:ignore
    {
        throw new AssertionFailed($message);
    }
}

<?php
// phpcs:ignoreFile
declare(strict_types=1);

namespace ListInterop\ConvertKit;

use ListInterop\ConvertKit\Exception\AssertionFailed;
use Webmozart\Assert\Assert as WebMozartAssert;

final class Assert extends WebMozartAssert
{
    /**
     * @param string $message
     *
     * @return never
     *
     * @throws AssertionFailed
     *
     * @psalm-pure
     */
    protected static function reportInvalidArgument($message)
    {
        throw new AssertionFailed($message);
    }
}

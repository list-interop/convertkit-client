<?php

declare(strict_types=1);

namespace ListInterop\ConvertKit\Test\Unit;

use JsonException;
use ListInterop\ConvertKit\Exception\AssertionFailed;
use ListInterop\ConvertKit\Exception\RuntimeError;
use ListInterop\ConvertKit\Util;
use PHPUnit\Framework\TestCase;
use Throwable;

class UtilTest extends TestCase
{
    public function testThatDatesCanBeConvertedInTheExpectedFormat(): void
    {
        $input = '2021-07-15T20:16:54.000Z';

        $date = Util::dateFromString($input);
        self::assertEquals(
            '2021-07-15 20:16:54 UTC',
            $date->format('Y-m-d H:i:s T'),
        );

        $expect = '2021-07-15T20:16:54.000+00:00';
        self::assertEquals($expect, Util::dateToString($date));
    }

    public function testDateConversionFailsWithUnexpectedValue(): void
    {
        $this->expectException(AssertionFailed::class);
        Util::dateFromString('2020-01-02');
    }

    public function testJsonDecodeErrorsAreWrapped(): void
    {
        try {
            Util::jsonToHash('{balls}');
        } catch (Throwable $error) {
            self::assertInstanceOf(RuntimeError::class, $error);
            self::assertInstanceOf(JsonException::class, $error->getPrevious());
            self::assertEquals($error->getPrevious()->getCode(), $error->getCode());
        }
    }
}

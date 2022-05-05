<?php

declare(strict_types=1);

namespace ListInterop\ConvertKit;

use DateTimeImmutable;
use DateTimeInterface;
use DateTimeZone;
use JsonException;
use ListInterop\ConvertKit\Exception\RuntimeError;

use function array_keys;
use function assert;
use function json_decode;
use function json_encode;
use function str_replace;

use const JSON_THROW_ON_ERROR;

final class Util
{
    /** @param non-empty-string $value */
    public static function dateFromString(string $value): DateTimeImmutable
    {
        /**
         * Dates from the API have a Z or z to indicate UTC.
         * The 'p' format string is not available prior to PHP 8.0
         */
        $value = str_replace(['z', 'Z'], '+00:00', $value);
        $date = DateTimeImmutable::createFromFormat(
            DateTimeInterface::RFC3339_EXTENDED,
            $value
        );

        Assert::isInstanceOf($date, DateTimeImmutable::class);

        return $date->setTimezone(new DateTimeZone('UTC'));
    }

    /** @return non-empty-string */
    public static function dateToString(DateTimeInterface $date): string
    {
        $value = $date->format(DateTimeInterface::RFC3339_EXTENDED);
        assert($value !== '');

        return $value;
    }

    /** @return array<string, mixed> */
    public static function jsonToHash(string $json): array
    {
        try {
            $data = json_decode($json, true, 10, JSON_THROW_ON_ERROR);
            Assert::isArray($data);
            Assert::allString(array_keys($data));
            /** @psalm-var array<string, mixed> $data */

            return $data;
        } catch (JsonException $e) {
            throw new RuntimeError('JSON decode failed', (int) $e->getCode(), $e);
        }
    }

    /** @param mixed $value */
    public static function jsonEncode($value): string
    {
        try {
            return json_encode($value, JSON_THROW_ON_ERROR);
        } catch (JsonException $error) {
            throw new RuntimeError('JSON encode failed', (int) $error->getCode(), $error);
        }
    }
}

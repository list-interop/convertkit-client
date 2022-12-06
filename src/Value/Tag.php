<?php

declare(strict_types=1);

namespace ListInterop\ConvertKit\Value;

use DateTimeImmutable;
use ListInterop\ConvertKit\Assert;
use ListInterop\ConvertKit\Util;

use function strtolower;

/**
 * @psalm-type TagArray = array{
 *     id: int,
 *     name: non-empty-string,
 *     created_at: non-empty-string
 * }
 */
final class Tag
{
    /** @param non-empty-string $name */
    private function __construct(
        private int $id,
        private string $name,
        private DateTimeImmutable $createdAt,
    ) {
    }

    /** @param array<array-key, mixed> $data */
    public static function fromArray(array $data): self
    {
        foreach (['id', 'name', 'created_at'] as $key) {
            Assert::keyExists($data, $key);
        }

        Assert::integer($data['id']);

        foreach (['name', 'created_at'] as $key) {
            Assert::string($data[$key]);
            Assert::notEmpty($data[$key]);
        }

        /** @psalm-var TagArray $data */

        return new self(
            $data['id'],
            $data['name'],
            Util::dateFromString($data['created_at']),
        );
    }

    /** @return non-empty-string */
    public function name(): string
    {
        return $this->name;
    }

    public function __toString(): string
    {
        return $this->name;
    }

    public function id(): int
    {
        return $this->id;
    }

    public function createdAt(): DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function matches(string $value): bool
    {
        return strtolower($this->name) === strtolower($value);
    }
}

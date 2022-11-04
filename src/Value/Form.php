<?php

declare(strict_types=1);

namespace ListInterop\ConvertKit\Value;

use DateTimeImmutable;
use ListInterop\ConvertKit\Assert;
use ListInterop\ConvertKit\Util;

/**
 * @psalm-type FormArray = array{
 *     id: int,
 *     name: non-empty-string,
 *     created_at: non-empty-string,
 *     type: non-empty-string,
 *     format: non-empty-string|null,
 *     embed_js: non-empty-string,
 *     embed_url: non-empty-string,
 *     archived: bool,
 *     uid: non-empty-string,
 * }
 */
final class Form
{
    /**
     * @param non-empty-string      $name
     * @param non-empty-string      $type
     * @param non-empty-string|null $format
     * @param non-empty-string      $embedJs
     * @param non-empty-string      $embedUrl
     * @param non-empty-string      $uid
     */
    private function __construct(
        private int $id,
        private string $name,
        private DateTimeImmutable $createdAt,
        private string $type,
        private string|null $format,
        private string $embedJs,
        private string $embedUrl,
        private bool $archived,
        private string $uid,
    ) {
    }

    /** @param array<string, mixed> $data */
    public static function fromArray(array $data): self
    {
        $keys = ['id', 'name', 'created_at', 'type', 'format', 'embed_js', 'embed_url', 'archived', 'uid'];
        foreach ($keys as $key) {
            Assert::keyExists($data, $key);
        }

        Assert::integer($data['id']);
        Assert::boolean($data['archived']);

        foreach (['name', 'created_at', 'type', 'embed_js', 'embed_url', 'uid'] as $key) {
            Assert::string($data[$key]);
            Assert::notEmpty($data[$key]);
        }

        Assert::nullOrString($data['format']);
        if ($data['format'] !== null) {
            Assert::notEmpty($data['format']);
        }

        /** @psalm-var FormArray $data */

        return new self(
            $data['id'],
            $data['name'],
            Util::dateFromString($data['created_at']),
            $data['type'],
            $data['format'],
            $data['embed_js'],
            $data['embed_url'],
            $data['archived'],
            $data['uid'],
        );
    }

    /** @return FormArray */
    public function toArray(): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'created_at' => Util::dateToString($this->createdAt),
            'type' => $this->type,
            'format' => $this->format,
            'embed_js' => $this->embedJs,
            'embed_url' => $this->embedUrl,
            'archived' => $this->archived,
            'uid' => $this->uid,
        ];
    }
}

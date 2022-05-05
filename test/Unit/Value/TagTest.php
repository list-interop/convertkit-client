<?php

declare(strict_types=1);

namespace ListInterop\ConvertKit\Test\Unit\Value;

use ListInterop\ConvertKit\Value\Tag;
use PHPUnit\Framework\TestCase;

class TagTest extends TestCase
{
    /** @var Tag */
    private $tag;

    protected function setUp(): void
    {
        parent::setUp();

        $this->tag = Tag::fromArray([
            'id' => 1,
            'name' => 'Foo',
            'created_at' => '2020-01-01T01:23:45.000Z',
        ]);
    }

    public function testIdIsExpectedValue(): void
    {
        self::assertEquals(1, $this->tag->id());
    }

    public function testNameHasExpectedValue(): void
    {
        self::assertEquals('Foo', $this->tag->name());
    }

    public function testDateIsExpectedValue(): void
    {
        self::assertEquals(
            '2020-01-01 01:23:45 UTC',
            $this->tag->createdAt()->format('Y-m-d H:i:s T')
        );
    }

    public function testMatchesIdentical(): void
    {
        self::assertTrue($this->tag->matches('Foo'));
    }

    public function testMatchesIsCaseInsensitive(): void
    {
        self::assertTrue($this->tag->matches('foO'));
    }

    public function testMatchesCanBeFalse(): void
    {
        self::assertFalse($this->tag->matches('Baz'));
    }

    public function testMatchesEmptyString(): void
    {
        self::assertFalse($this->tag->matches(''));
    }

    public function testCanBeCastToString(): void
    {
        self::assertEquals('Foo', $this->tag->__toString());
    }
}

<?php

declare(strict_types=1);

namespace ListInterop\ConvertKit\Test\Unit\Value;

use ListInterop\ConvertKit\Value\Form;
use PHPUnit\Framework\TestCase;

class FormTest extends TestCase
{
    /** @var Form */
    private $form;

    protected function setUp(): void
    {
        parent::setUp();

        $this->form = Form::fromArray([
            'id' => 123,
            'name' => 'Name',
            'created_at' => '2020-01-01T01:23:45.000Z',
            'type' => 'Type',
            'format' => 'Format',
            'embed_js' => 'JS',
            'embed_url' => 'URL',
            'archived' => false,
            'uid' => 'UID',
        ]);
    }

    public function testExpectedValues(): void
    {
        $values = $this->form->toArray();
        self::assertEquals(123, $values['id']);
        self::assertEquals('Name', $values['name']);
        self::assertEquals('2020-01-01T01:23:45.000+00:00', $values['created_at']);
        self::assertEquals('Type', $values['type']);
        self::assertEquals('Format', $values['format']);
        self::assertEquals('JS', $values['embed_js']);
        self::assertEquals('URL', $values['embed_url']);
        self::assertFalse($values['archived']);
        self::assertEquals('UID', $values['uid']);
    }
}

<?php

declare(strict_types=1);

namespace ListInterop\ConvertKit\Test\Integration;

use Laminas\Diactoros\StreamFactory;
use Laminas\Diactoros\UriFactory;
use ListInterop\ConvertKit\Client;
use ListInterop\ConvertKit\Exception\ApiError;
use ListInterop\ConvertKit\Util;
use ListInterop\ConvertKit\Value\Form;
use ListInterop\ConvertKit\Value\Tag;

class ClientTest extends RemoteIntegrationTestCase
{
    private Client $client;

    protected function setUp(): void
    {
        parent::setUp();

        $this->client = new Client(
            MockServer::VALID_KEY,
            MockServer::VALID_SECRET,
            $this->httpClient(),
            $this->requestFactory(),
            new UriFactory(),
            new StreamFactory(),
            self::apiServerUri(),
        );
    }

    public function testThatAFormCanBeRetrievedById(): void
    {
        $form = $this->client->findFormById(1);
        self::assertInstanceOf(Form::class, $form);
    }

    public function testThatAnExceptionIsThrownWhenAFormCannotBeFound(): void
    {
        $this->expectException(ApiError::class);
        $this->expectExceptionCode(404);
        $this->client->findFormById(2);
    }

    public function testThatAListOfTagsCanBeRetrieved(): void
    {
        $tags = $this->client->tagList();
        self::assertContainsOnlyInstancesOf(Tag::class, $tags);
        self::assertCount(2, $tags);
    }

    public function testThatKnownTagsCanBeFoundByName(): void
    {
        self::assertInstanceOf(Tag::class, $this->client->findTagByName('tag 1'));
        self::assertInstanceOf(Tag::class, $this->client->findTagByName('tag 2'));
    }

    public function testThatUnknownTagNamesWillBeNull(): void
    {
        self::assertNull($this->client->findTagByName('Foo'));
    }

    /** @return array<string, mixed> */
    private function lastRequestJsonBody(): array
    {
        $request = $this->httpClient()->lastRequest();
        self::assertNotNull($request);

        return Util::jsonToHash((string) $request->getBody());
    }

    public function testThatTagsCanBeCreated(): void
    {
        $this->client->createTag('Baz', 'Bomb');
        $body = $this->lastRequestJsonBody();
        $expect = [
            'tag' => [
                ['name' => 'Baz'],
                ['name' => 'Bomb'],
            ],
        ];

        self::assertEquals($expect, $body);
    }

    public function testThatSubscribingToAFormCanBeSuccessful(): void
    {
        $this->client->subscribeToForm(1, 'me@example.com', 'Jim', []);
        $body = $this->lastRequestJsonBody();
        $expect = [
            'email' => 'me@example.com',
            'first_name' => 'Jim',
        ];

        self::assertEquals($expect, $body);
    }

    public function testThatSubscribingToAFormWithAnUnknownTagIsIgnored(): void
    {
        $this->client->subscribeToForm(1, 'me@example.com', 'Jim', ['unknown']);
        $body = $this->lastRequestJsonBody();
        $expect = [
            'email' => 'me@example.com',
            'first_name' => 'Jim',
        ];

        self::assertEquals($expect, $body);
    }

    public function testThatSubscribingToAFormWithValidTagsYieldsTheTagsInThePostBody(): void
    {
        $this->client->subscribeToForm(1, 'me@example.com', 'Jim', ['tag 1', 'Tag 2']);
        $body = $this->lastRequestJsonBody();
        $expect = [
            'email' => 'me@example.com',
            'first_name' => 'Jim',
            'tags' => [
                123, // Tags are looked up and converted to their ID - This ID can be found in the Mock Server
                456,
            ],
        ];

        self::assertEquals($expect, $body);
    }

    public function testSubscribingToAFormWithoutAName(): void
    {
        $this->client->subscribeToForm(1, 'me@example.com', null, []);
        $body = $this->lastRequestJsonBody();
        $expect = ['email' => 'me@example.com'];

        self::assertEquals($expect, $body);
    }

    public function testSubscribingToAFormWithTagIdentifiers(): void
    {
        $this->client->subscribeToForm(1, 'me@example.com', 'Jim', [123, 999]);
        $body = $this->lastRequestJsonBody();
        $expect = [
            'email' => 'me@example.com',
            'first_name' => 'Jim',
            'tags' => [123],
        ];

        self::assertEquals($expect, $body);
    }
}

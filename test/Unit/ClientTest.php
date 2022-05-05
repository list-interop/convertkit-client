<?php

declare(strict_types=1);

namespace ListInterop\ConvertKit\Test\Unit;

use Laminas\Diactoros\RequestFactory;
use Laminas\Diactoros\StreamFactory;
use ListInterop\ConvertKit\Client;
use ListInterop\ConvertKit\Exception\RequestFailure;
use ListInterop\ConvertKit\Test\Unit\Stub\UriFactory;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Client\NetworkExceptionInterface;

class ClientTest extends TestCase
{
    /** @var Client */
    private $client;
    /** @var MockObject&ClientInterface */
    private $httpClient;

    protected function setUp(): void
    {
        parent::setUp();
        $this->httpClient = $this->createMock(ClientInterface::class);
        $this->client = new Client(
            'AnyKey',
            'OtherKey',
            $this->httpClient,
            new RequestFactory(),
            new UriFactory(),
            new StreamFactory(),
            'http://0.0.0.0'
        );
    }

    public function testThatTheBaseUriWillBeTrimmedDuringConstructionSoThatAppendingPathsWorks(): void
    {
        $factory = new UriFactory();
        new Client(
            'AnyKey',
            'OtherKey',
            $this->createMock(ClientInterface::class),
            new RequestFactory(),
            $factory,
            new StreamFactory(),
            'http://0.0.0.0/some/path//'
        );

        self::assertEquals('http://0.0.0.0/some/path', (string) $factory->lastUri());
    }

    public function testThatNetworkErrorsWillBeWrapped(): void
    {
        $error = $this->createMock(NetworkExceptionInterface::class);
        $this->httpClient->expects(self::atLeast(1))
            ->method('sendRequest')
            ->willThrowException($error);

        try {
            $this->client->tagList();
            self::fail('No exception was thrown');
        } catch (RequestFailure $expect) {
            self::assertSame($error, $expect->getPrevious());
            $request = $expect->failedRequest();
            self::assertStringContainsString('/tags', (string) $request->getUri());
        }
    }
}

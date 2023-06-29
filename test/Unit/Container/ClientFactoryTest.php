<?php

declare(strict_types=1);

namespace ListInterop\ConvertKit\Test\Unit\Container;

use Http\Client\Curl\Client;
use Laminas\Diactoros\RequestFactory;
use Laminas\Diactoros\StreamFactory;
use Laminas\Diactoros\UriFactory;
use ListInterop\ConvertKit\Container\ClientFactory;
use ListInterop\ConvertKit\Exception\AssertionFailed;
use PHPUnit\Framework\MockObject\MockObject;
use PHPUnit\Framework\TestCase;
use Psr\Container\ContainerInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

class ClientFactoryTest extends TestCase
{
    private ClientFactory $factory;
    private ContainerInterface&MockObject $container;

    protected function setUp(): void
    {
        parent::setUp();

        $this->factory = new ClientFactory();
        $this->container = $this->createMock(ContainerInterface::class);
    }

    /** @return array<string, array{0: bool, 1: mixed, 2: string}> */
    public static function erroneousConfig(): array
    {
        return [
            'No Config' => [
                false,
                null,
                'No configuration can be retrieved from the given container',
            ],
            'Null Config' => [
                true,
                null,
                'No configuration can be retrieved from the given container',
            ],
            'Empty Config' => [
                true,
                [],
                'Missing configuration `convertkit`',
            ],
            'String top level, ffs' => [
                true,
                ['convertkit' => 'foo'],
                'Missing configuration `convertkit`',
            ],
            'Missing key' => [
                true,
                ['convertkit' => []],
                'No API key has been configured',
            ],
            'Key not string' => [
                true,
                ['convertkit' => ['api-key' => 1]],
                'No API key has been configured',
            ],
            'Empty key' => [
                true,
                ['convertkit' => ['api-key' => '']],
                'The API key is an empty string',
            ],
            'Missing Secret' => [
                true,
                ['convertkit' => ['api-key' => 'foo']],
                'No secret key has been configured',
            ],
            'Secret not string' => [
                true,
                ['convertkit' => ['api-key' => 'foo', 'secret-key' => 1]],
                'No secret key has been configured',
            ],
            'Empty Secret' => [
                true,
                ['convertkit' => ['api-key' => 'foo', 'secret-key' => '']],
                'The secret key is an empty string',
            ],
        ];
    }

    /** @dataProvider erroneousConfig */
    public function testThatTheContainerMustHaveConfiguration(bool $has, mixed $get, string $expectedErrorMessage): void
    {
        $this->container->expects(self::once())
            ->method('has')
            ->with('config')
            ->willReturn($has);

        if ($has) {
            $this->container->expects(self::once())
                ->method('get')
                ->with('config')
                ->willReturn($get);
        } else {
            $this->container->expects(self::never())
                ->method('get');
        }

        $this->expectException(AssertionFailed::class);
        $this->expectExceptionMessage($expectedErrorMessage);
        ($this->factory)($this->container);
    }

    public function testClientCreationWillProceedWhenTheContainerHasAllRequiredDependencies(): void
    {
        $this->container->expects(self::exactly(5))
            ->method('has')
            ->willReturn(true);

        $this->container->expects(self::exactly(5))
            ->method('get')
            ->willReturnMap([
                ['config', ['convertkit' => ['api-key' => 'foo', 'secret-key' => 'bar']]],
                [ClientInterface::class, new Client()],
                [RequestFactoryInterface::class, new RequestFactory()],
                [UriFactoryInterface::class, new UriFactory()],
                [StreamFactoryInterface::class, new StreamFactory()],
            ]);

        ($this->factory)($this->container);
    }

    public function testClientCreationWillProceedWhenOnlyConfigIsAvailable(): void
    {
        $this->container->expects(self::exactly(5))
            ->method('has')
            ->willReturnMap([
                ['config', true],
                [ClientInterface::class, false],
                [RequestFactoryInterface::class, false],
                [UriFactoryInterface::class, false],
                [StreamFactoryInterface::class, false],
            ]);
        $this->container->expects(self::once())
            ->method('get')
            ->willReturn(['convertkit' => ['api-key' => 'foo', 'secret-key' => 'bar']]);

        ($this->factory)($this->container);
    }

    public function testAnAssertionErrorWillBeThrownWhenTheContainerSendsSomethingWeird(): void
    {
        $this->container->expects(self::exactly(2))
            ->method('has')
            ->willReturn(true);

        $this->container->expects(self::exactly(2))
            ->method('get')
            ->willReturnMap([
                ['config', ['convertkit' => ['api-key' => 'foo', 'secret-key' => 'bar']]],
                [ClientInterface::class, 'Not the right thing'],
            ]);

        $this->expectException(AssertionFailed::class);
        ($this->factory)($this->container);
    }
}

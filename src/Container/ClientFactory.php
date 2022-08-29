<?php

declare(strict_types=1);

namespace ListInterop\ConvertKit\Container;

use Http\Discovery\Psr17FactoryDiscovery;
use Http\Discovery\Psr18ClientDiscovery;
use ListInterop\ConvertKit\Assert;
use ListInterop\ConvertKit\Client;
use Psr\Container\ContainerInterface;
use Psr\Http\Client\ClientInterface;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;

use function is_a;

/**
 * PSR-11 Container Factory
 *
 * If you use one of the many available PSR-11 containers, this factory might be useful to you.
 *
 * It assumes that array configuration can be retrieved with the id `config` and that this config array contains api
 * keys for ConvertKit under `$config['convertkit']['api-key']` and `$config['convertkit']['secret-key']`
 *
 * The other dependencies required by the API client, are a PSR-18 HTTP Client and various PSR-17 factories.
 * The factory will query the container for all of these factories and the HTTP client and fall back to "Discover"
 * those dependencies as provided by popular libraries using the `php-http/discovery` library/tool.
 */
final class ClientFactory
{
    public function __invoke(ContainerInterface $container): Client
    {
        $config = $container->has('config') ? $container->get('config') : null;
        Assert::isArray($config, 'No configuration can be retrieved from the given container');
        /** @psalm-var array|null $config */
        $config = $config['convertkit'] ?? null;
        Assert::isArray($config, 'Missing configuration `convertkit`');
        $apiKey = $config['api-key'] ?? null;
        Assert::string($apiKey, 'No API key has been configured');
        Assert::notEmpty($apiKey, 'The API key is an empty string');
        $secretKey = $config['secret-key'] ?? null;
        Assert::string($secretKey, 'No secret key has been configured');
        Assert::notEmpty($secretKey, 'The secret key is an empty string');

        return new Client(
            $apiKey,
            $secretKey,
            $this->serviceOrNull($container, ClientInterface::class)
                ?: Psr18ClientDiscovery::find(),
            $this->serviceOrNull($container, RequestFactoryInterface::class)
                ?: Psr17FactoryDiscovery::findRequestFactory(),
            $this->serviceOrNull($container, UriFactoryInterface::class)
                ?: Psr17FactoryDiscovery::findUriFactory(),
            $this->serviceOrNull($container, StreamFactoryInterface::class)
                ?: Psr17FactoryDiscovery::findStreamFactory(),
        );
    }

    /**
     * @psalm-param class-string<T> $serviceName
     *
     * @psalm-return T|null
     *
     * @psalm-template T
     */
    private function serviceOrNull(ContainerInterface $container, string $serviceName): ?object
    {
        /** @psalm-var T|null $service */
        $service = $container->has($serviceName)
            ? $container->get($serviceName)
            : null;

        Assert::true((is_a($service, $serviceName) || $service === null));

        return $service;
    }
}

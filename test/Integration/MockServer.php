<?php

declare(strict_types=1);

namespace ListInterop\ConvertKit\Test\Integration;

use Fig\Http\Message\RequestMethodInterface as Method;
use Laminas\Diactoros\Uri;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use React\EventLoop\Loop;
use React\EventLoop\LoopInterface;
use React\Http\HttpServer;
use React\Http\Message\Response;
use React\Socket\SocketServer;

use function is_callable;
use function sprintf;

final class MockServer
{
    public const VALID_KEY = 'valid_key';
    public const VALID_SECRET = 'valid_secret';

    /** @var LoopInterface */
    private $loop;
    /** @var HttpServer */
    private $server;
    /** @var SocketServer */
    private $socket;

    /**
     * Seconds before the server shuts down automatically
     *
     * @var int
     */
    private $timeout = 10;

    /** @var array<string, array{uri:string, method: string, body: string, type: string, code: int, bodyMatcher: callable|null}> */
    private $responses;
    /** @var string */
    private $basePath;

    public function __construct(int $port, string $basePath)
    {
        $this->basePath = $basePath;
        $this->seedResponses();
        $this->loop = Loop::get();
        $this->server = new HttpServer($this->loop, function (RequestInterface $request): ResponseInterface {
            return $this->handleRequest($request);
        });
        $this->socket = new SocketServer(sprintf('0.0.0.0:%d', $port), [], $this->loop);
        $this->server->listen($this->socket);
    }

    public function start(): void
    {
        $this->loop->addTimer($this->timeout, function (): void {
            $this->stop();
        });
        $this->loop->run();
    }

    public function stop(): void
    {
        $this->loop->stop();
        $this->server->removeAllListeners();
        $this->socket->close();
    }

    private function handleRequest(RequestInterface $request): ResponseInterface
    {
        $data = $this->matchUri($request);

        return new Response($data['code'], ['Content-Type' => $data['type']], $data['body']);
    }

    /** @return array{uri:string, method: string, body: string, type: string, code: int, bodyMatcher: callable|null} */
    private function matchUri(RequestInterface $request): array
    {
        foreach ($this->responses as $data) {
            $matchUri = new Uri($this->basePath . $data['uri']);
            if ($request->getUri()->getPath() !== $matchUri->getPath()) {
                continue; // Paths don't match
            }

            if ($request->getMethod() !== $data['method']) {
                continue;
            }

            if ($request->getUri()->getQuery() !== $matchUri->getQuery()) {
                continue;
            }

            $body = (string) $request->getBody();
            if (is_callable($data['bodyMatcher']) && $data['bodyMatcher']($body) === false) {
                continue;
            }

            return $data;
        }

        return [
            'uri' => $request->getUri()->getPath(),
            'method' => 'GET',
            'body' => 'NOT FOUND: ' . $request->getUri()->getPath(),
            'type' => 'text/plain',
            'code' => 404,
            'bodyMatcher' => null,
        ];
    }

    private function seedResponses(): void
    {
        $this->responses = [
            'Ping' => [
                'uri' => '/ping',
                'method' => Method::METHOD_GET,
                'body' => 'pong',
                'type' => 'text/plain',
                'code' => 200,
                'bodyMatcher' => null,
            ],
            'Existing Form' => [
                'uri' => sprintf('/forms/1?api_key=%s', self::VALID_KEY),
                'method' => Method::METHOD_GET,
                'body' => '{"id":1234,"name":"Form Name","created_at":"2020-01-01T20:30:40.000Z","type":"hosted","format":null,"embed_js":"https://somewhere.ck.page/foo/index.js","embed_url":"https://somewhere.ck.page/foo","archived":false,"uid":"foo"}',
                'type' => 'application/json',
                'code' => 200,
                'bodyMatcher' => null,
            ],
            'Form Not Found' => [
                'uri' => sprintf('/forms/2?api_key=%s', self::VALID_KEY),
                'method' => Method::METHOD_GET,
                'body' => '{"error":"Not Found","message":"The entity you were trying to find doesn\'t exist"}',
                'type' => 'application/json',
                'code' => 404,
                'bodyMatcher' => null,
            ],
            'List Tags' => [
                'uri' => sprintf('/tags?api_key=%s', self::VALID_KEY),
                'method' => Method::METHOD_GET,
                'body' => '{"tags":[{"id":123,"name":"Tag 1","created_at":"2022-05-05T11:45:26.000Z"},{"id":456,"name":"Tag 2","created_at":"2022-05-05T11:45:33.000Z"}]}',
                'type' => 'application/json',
                'code' => 200,
                'bodyMatcher' => null,
            ],
            'Create Tags' => [
                'uri' => sprintf('/tags?api_secret=%s', self::VALID_SECRET),
                'method' => Method::METHOD_POST,
                'body' => '[{"id":567,"name":"Tag 4","created_at":"2022-05-05T12:43:47.000Z"},{"id":890,"name":"Tag 5","created_at":"2022-05-05T12:43:47.000Z"}]',
                'type' => 'application/json',
                'code' => 201,
                'bodyMatcher' => null,
            ],
            'Successful Subscription' => [
                'uri' => sprintf('/forms/1/subscribe?api_key=%s', self::VALID_KEY),
                'method' => Method::METHOD_POST,
                'body' => '{"subscription":{"id":123,"state":"inactive","created_at":"2022-05-05T14:49:20.000Z","source":"API::V3::SubscriptionsController (external)","referrer":null,"subscribable_id":123,"subscribable_type":"form","subscriber":{"id":123}}}',
                'type' => 'application/json',
                'code' => 200,
                'bodyMatcher' => null,
            ],
        ];
    }
}

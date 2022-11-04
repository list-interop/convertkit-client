<?php

declare(strict_types=1);

namespace ListInterop\ConvertKit;

use Fig\Http\Message\RequestMethodInterface as Method;
use ListInterop\ConvertKit\Exception\ApiError;
use ListInterop\ConvertKit\Exception\RequestFailure;
use ListInterop\ConvertKit\Value\Form;
use ListInterop\ConvertKit\Value\Tag;
use Psr\Http\Client\ClientExceptionInterface as PsrHttpError;
use Psr\Http\Client\ClientInterface as HttpClient;
use Psr\Http\Message\RequestFactoryInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Psr\Http\Message\StreamFactoryInterface;
use Psr\Http\Message\UriFactoryInterface;
use Psr\Http\Message\UriInterface;

use function array_map;
use function http_build_query;
use function is_string;
use function rtrim;
use function sprintf;

final class Client
{
    protected const BASE_URI = 'https://api.convertkit.com/v3';

    private UriInterface $baseUri;

    /**
     * @param non-empty-string $apiKey
     * @param non-empty-string $apiSecret
     * @param non-empty-string $baseUri
     */
    public function __construct(
        private string $apiKey,
        private string $apiSecret,
        private HttpClient $httpClient,
        private RequestFactoryInterface $requestFactory,
        UriFactoryInterface $uriFactory,
        private StreamFactoryInterface $streamFactory,
        string $baseUri = self::BASE_URI,
    ) {
        $this->baseUri = $uriFactory->createUri(rtrim($baseUri, '/'));
    }

    public function findFormById(int $id): Form
    {
        $path = sprintf('/forms/%d', $id);
        $request = $this->requestFactory(Method::METHOD_GET, $path, false);
        $response = $this->send($request);

        return Form::fromArray(Util::jsonToHash((string) $response->getBody()));
    }

    /** @return list<Tag> */
    public function tagList(): array
    {
        $request = $this->requestFactory(Method::METHOD_GET, '/tags', false);
        $response = $this->send($request);
        $data = Util::jsonToHash((string) $response->getBody());
        Assert::keyExists($data, 'tags');
        Assert::isArray($data['tags']);
        $list = [];
        foreach ($data['tags'] as $tag) {
            Assert::isArray($tag);
            $list[] = Tag::fromArray($tag);
        }

        return $list;
    }

    public function createTag(string ...$names): void
    {
        $makeTag = static fn (string $name): array => ['name' => $name];
        $payload = Util::jsonEncode([
            'tag' => array_map($makeTag, $names),
        ]);

        $request = $this->requestFactory(Method::METHOD_POST, '/tags', true)
            ->withBody($this->streamFactory->createStream($payload));
        $this->send($request);
    }

    public function findTagByName(string $name): Tag|null
    {
        foreach ($this->tagList() as $tag) {
            if (! $tag->matches($name)) {
                continue;
            }

            return $tag;
        }

        return null;
    }

    /**
     * @param non-empty-string           $email
     * @param non-empty-string|null      $firstName
     * @param list<non-empty-string|int> $tagNames
     */
    public function subscribeToForm(int $formId, string $email, string|null $firstName, array $tagNames): void
    {
        Assert::email($email);
        $path = sprintf('/forms/%d/subscribe', $formId);
        $payload = ['email' => $email];
        if ($firstName) {
            $payload['first_name'] = $firstName;
        }

        $tags = $this->tagIdentifiersFrom($tagNames);
        if ($tags !== []) {
            $payload['tags'] = $tags;
        }

        $payload = Util::jsonEncode($payload);
        $request = $this->requestFactory(Method::METHOD_POST, $path, false)
            ->withBody($this->streamFactory->createStream($payload));
        $this->send($request);
    }

    /**
     * @param int|non-empty-string $value
     * @param list<Tag>            $tags
     */
    private function findTagIn(int|string $value, array $tags): Tag|null
    {
        foreach ($tags as $tag) {
            if ($tag->id() === $value) {
                return $tag;
            }

            if (! is_string($value) || ! $tag->matches($value)) {
                continue;
            }

            return $tag;
        }

        return null;
    }

    /**
     * @param list<int|non-empty-string> $tags
     *
     * @return list<int>
     */
    private function tagIdentifiersFrom(array $tags): array
    {
        $list = [];
        $existing = $this->tagList();
        foreach ($tags as $value) {
            $tag = $this->findTagIn($value, $existing);
            if (! $tag) {
                continue;
            }

            $list[] = $tag->id();
        }

        return $list;
    }

    private function requestFactory(string $method, string $path, bool $secretKey): RequestInterface
    {
        $query = $secretKey ? ['api_secret' => $this->apiSecret] : ['api_key' => $this->apiKey];
        $uri = $this->baseUri->withPath(
            $this->baseUri->getPath() . $path,
        )->withQuery(http_build_query($query));

        return $this->requestFactory->createRequest($method, $uri);
    }

    private function send(RequestInterface $request): ResponseInterface
    {
        $request = $request->withHeader('Accept', 'application/json');

        $body = (string) $request->getBody();
        if ($body !== '') {
            $request = $request->withHeader('Content-Type', 'application/json');
        }

        try {
            $response = $this->httpClient->sendRequest($request);
        } catch (PsrHttpError $error) {
            throw RequestFailure::withPsrError($request, $error);
        }

        if ($response->getStatusCode() >= 299) {
            throw ApiError::fromExchange($request, $response);
        }

        return $response;
    }
}

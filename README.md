# ConvertKit PHP API Client

[![Build Status](https://github.com/list-interop/convertkit-client/workflows/Continuous%20Integration/badge.svg)](https://github.com/list-interop/convertkit-client/actions?query=workflow%3A"Continuous+Integration")

[![codecov](https://codecov.io/gh/list-interop/convertkit-client/branch/main/graph/badge.svg)](https://codecov.io/gh/list-interop/convertkit-client)
[![Psalm Type Coverage](https://shepherd.dev/github/list-interop/convertkit-client/coverage.svg)](https://shepherd.dev/github/list-interop/convertkit-client)

[![Latest Stable Version](https://poser.pugx.org/list-interop/convertkit-client/v/stable)](https://packagist.org/packages/list-interop/convertkit-client)
[![Total Downloads](https://poser.pugx.org/list-interop/convertkit-client/downloads)](https://packagist.org/packages/list-interop/convertkit-client)

## Introduction

This is an API Client for the [ConvertKit](https://convertkit.com) mailing list service for PHP versions 7.4 and up

There are several clients available on Packagist, but the main motivation here is being agnostic to the HTTP client in use.

The client uses PSR17 and 18 standards, so you can bring your own preferred libs.

## Implemented Features

- [x] Retrieve form by id
- [x] Subscribe to a form _(Custom fields are not yet available)_
- [x] List and find tags
- [x] Create Tags
- [ ] List custom fields
- [ ] Crud for custom fields
- [ ] Fetch form subscriber
- [ ] Other Crud operations for subscribers
- [ ] Tagging subscribers post subscribe.
- [ ] Stuff regarding webhooks and purchases…

## Roadmap

It'd be nice to work up the rest of the available features in the API, but it probably won't happen very quickly, I'm more likely to work on different implementations first to firm up the spec there so that stable releases can be made. Shipping a caching client using a psr cache pool would be handy for those aspects of the API that rarely change. It would also be quite trivial to implement.

## Installation

Composer is the only supported installation method…

As previously mentioned, you'll need a [PSR-18 HTTP Client](https://packagist.org/providers/psr/http-client-implementation) first and also [PSR-7 and PSR-17 implementations](https://packagist.org/providers/psr/http-factory-implementation). For example:

```shell
composer require php-http/curl-client
composer require laminas/laminas-diactoros
```

You'll then be able to install this with:

```shell
composer require list-interop/convertkit-client
```

## Usage

Docs are admittedly thin on the ground.

The lib ships with a PSR11 factory that you can integrate with your container of choice. It falls back to discovery for whatever PSR-7/17/18 stuff that you have installed.

Ultimately, you'll need API Keys to get going, and assuming you can provide the `Client` constructor with its required constructor dependencies, you'll be able to start issuing commands and getting results:

### Add a subscriber…

```php
use ListInterop\ConvertKit\Client;
use ListInterop\ConvertKit\Exception\ApiError;
use ListInterop\ConvertKit\Exception\ConvertKitError;
use ListInterop\ConvertKit\Exception\RequestFailure;

assert($client instanceof Client);

$formId = 123; // Retrieve this from the dashboard or by inspecting the forms returned by the api.

try {
    $client->subscribeToForm($formId, 'me@example.com', 'Fred', ['tag 1', 'tag 2']);
} catch (RequestFailure $error) {
    // Network error - can't reach ConvertKit
} catch (ApiError $error) {
    // Something was wrong with the values provided, or your API key was wonky
    // i.e. The API rejected your request
} catch (ConvertKitError $error) {
    // Generic Error, Assertion failed etc.
    // All exceptions implement this interface, Providing an invalid email address will get you here.
}

```

You should find that exceptions are consistent and meaningful, but for now, to find out what those are, you'll need to look at the source.

## Contributions

Are most welcome, but please make sure that pull requests include relevant tests. There's a handy composer script you can run locally:

```shell
composer check
```

… which will check coding standards, run psalm and phpunit in order.

## License

[MIT Licensed](LICENSE.md).

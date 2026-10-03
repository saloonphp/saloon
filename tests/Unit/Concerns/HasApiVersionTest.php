<?php

declare(strict_types=1);

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Connector;
use Saloon\Enums\VersionMode;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Traits\Plugins\HasApiVersion;

test('header mode adds the version to the outgoing request headers', function () {
    $mockClient = sendVersionedRequest(
        makeVersionedConnector('https://api.provider.com', VersionMode::Header, 'anthropic-version'),
        makeVersionedRequest(),
    );

    $mockClient->assertSent(function (Request $request, $response) {
        expect($response->getPsrRequest()->getHeaderLine('api-version'))->toBe('anthropic-version');

        return true;
    });
});

test('query param mode appends the version to the query string', function () {
    $mockClient = sendVersionedRequest(
        makeVersionedConnector('https://api.provider.com', VersionMode::QueryParam, '2026-10'),
        makeVersionedRequest(),
    );

    $mockClient->assertSent(function (Request $request, $response) {
        expect($response->getPsrRequest()->getUri()->getQuery())->toBe('api-version=2026-10');

        return true;
    });
});

test('url path mode replaces the version placeholder in the base url', function () {
    $mockClient = sendVersionedRequest(
        makeVersionedConnector('https://generativelanguage.googleapis.com/{version}', VersionMode::Url, 'v1beta'),
        makeVersionedRequest(),
    );

    $mockClient->assertSent(function (Request $request, $response) {
        expect($response->getPendingRequest()->getUrl())->toBe('https://generativelanguage.googleapis.com/v1beta');
        expect((string) $response->getPsrRequest()->getUri())->toBe('https://generativelanguage.googleapis.com/v1beta');

        return true;
    });
});

test('subdomain mode replaces the version placeholder in the host', function () {
    $mockClient = sendVersionedRequest(
        makeVersionedConnector('https://{version}.api.provider.com', VersionMode::Url, 'v2'),
        makeVersionedRequest(),
    );

    $mockClient->assertSent(function (Request $request, $response) {
        expect($response->getPendingRequest()->getUrl())->toBe('https://v2.api.provider.com');
        expect((string) $response->getPsrRequest()->getUri())->toBe('https://v2.api.provider.com');

        return true;
    });
});

test('the connector can override the api version at runtime', function () {
    $connector = makeVersionedConnector('https://api.provider.com', VersionMode::Header, 'anthropic-version');
    $connector->setApiVersion('override-version');

    $mockClient = sendVersionedRequest($connector, makeVersionedRequest());

    $mockClient->assertSent(function (Request $request, $response) {
        expect($response->getPsrRequest()->getHeaderLine('api-version'))->toBe('override-version');

        return true;
    });
});

test('the request can override the api version at runtime', function () {
    $request = makeVersionedRequest('override-version', VersionMode::Header);

    $mockClient = sendVersionedRequest(
        makeVersionedConnector('https://api.provider.com'),
        $request,
    );

    $mockClient->assertSent(function (Request $request, $response) {
        expect($response->getPsrRequest()->getHeaderLine('api-version'))->toBe('override-version');

        return true;
    });
});

test('a null api version does not add a header, query parameter, or replace the url', function (VersionMode $versionMode) {
    $mockClient = sendVersionedRequest(
        makeVersionedConnector('https://api.provider.com/{version}', $versionMode),
        makeVersionedRequest(null, $versionMode),
    );

    $mockClient->assertSent(function (Request $request, $response) {
        expect($response->getPsrRequest()->getHeaderLine('api-version'))->toBe('');
        expect($response->getPsrRequest()->getUri()->getQuery())->not->toContain('api-version');
        expect($response->getPendingRequest()->getUrl())->toBe('https://api.provider.com/{version}');

        return true;
    });
})->with([
    'header' => VersionMode::Header,
    'query param' => VersionMode::QueryParam,
    'url' => VersionMode::Url,
]);

test('url mode rejects versions that could alter the scheme, host, or path', function (string $baseUrl, string $version) {
    $mockClient = new MockClient([MockResponse::make()]);

    $connector = makeVersionedConnector($baseUrl, VersionMode::Url, $version);

    expect(fn () => $connector->send(makeVersionedRequest(), $mockClient))
        ->toThrow(InvalidArgumentException::class, sprintf('The API version "%s" is not safe to use in a URL.', $version));

    $mockClient->assertNothingSent();
})->with([
    'https scheme in path' => ['https://api.provider.com/{version}', 'https://evil.com'],
    'https scheme in subdomain' => ['https://{version}.api.provider.com', 'https://evil.com'],
    'file scheme' => ['{version}/api', 'file:///etc/passwd'],
    'bare scheme separator' => ['https://api.provider.com/{version}', '://evil.com'],
    'protocol relative host' => ['https://api.provider.com/{version}', '//evil.com'],
    'userinfo host takeover' => ['https://{version}.api.provider.com', 'evil.com@'],
    'path traversal' => ['https://api.provider.com/{version}', '..'],
    'nested path traversal' => ['https://api.provider.com/{version}', 'v1/../../admin'],
    'single dot' => ['https://api.provider.com/{version}', '.'],
    'query string' => ['https://api.provider.com/{version}', 'v1?admin=true'],
    'fragment' => ['https://api.provider.com/{version}', 'v1#admin'],
    'port' => ['https://{version}.api.provider.com', 'evil.com:8080'],
    'whitespace' => ['https://api.provider.com/{version}', 'v1 beta'],
    'crlf' => ['https://api.provider.com/{version}', "v1\r\nHost: evil.com"],
    'percent encoding' => ['https://api.provider.com/{version}', '%2e%2e'],
]);

test('url mode accepts common version formats', function (string $version) {
    $mockClient = sendVersionedRequest(
        makeVersionedConnector('https://api.provider.com/{version}', VersionMode::Url, $version),
        makeVersionedRequest(),
    );

    $mockClient->assertSent(function (Request $request, $response) use ($version) {
        expect($response->getPendingRequest()->getUrl())->toBe('https://api.provider.com/' . $version);

        return true;
    });
})->with(['v1', 'v1beta', 'v2.1', '2026-10-01', 'v1_alpha']);

function makeVersionedConnector(string $baseUrl, VersionMode $versionMode = VersionMode::Header, ?string $apiVersion = null): Connector
{
    return new class($baseUrl, $versionMode, $apiVersion) extends Connector {
        use HasApiVersion;

        public function __construct(
            private readonly string $baseUrl,
            VersionMode $versionMode,
            ?string $apiVersion,
        ) {
            $this->versionMode = $versionMode;

            if (is_string($apiVersion)) {
                $this->setApiVersion($apiVersion);
            }
        }

        public function resolveBaseUrl(): string
        {
            return $this->baseUrl;
        }
    };
}

function makeVersionedRequest(?string $apiVersion = null, VersionMode $versionMode = VersionMode::Header): Request
{
    return new class($versionMode, $apiVersion) extends Request {
        use HasApiVersion;

        protected Method $method = Method::GET;

        public function __construct(
            VersionMode $versionMode,
            ?string $apiVersion,
        ) {
            $this->versionMode = $versionMode;

            if (is_string($apiVersion)) {
                $this->setApiVersion($apiVersion);
            }
        }

        public function resolveEndpoint(): string
        {
            return '';
        }
    };
}

function sendVersionedRequest(Connector $connector, Request $request): MockClient
{
    $mockClient = new MockClient([
        MockResponse::make(),
    ]);

    $connector->send($request, $mockClient);

    return $mockClient;
}

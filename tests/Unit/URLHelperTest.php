<?php

declare(strict_types=1);

use Saloon\Helpers\URLHelper;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Tests\Fixtures\Requests\AbsoluteEndpointRequest;

test('the URL helper will join two URLs together', function ($baseUrl, $endpoint, $expected) {
    expect(URLHelper::join($baseUrl, $endpoint))->toBe($expected);
})->with([
    ['https://google.com', '/search', 'https://google.com/search'],
    ['https://google.com', 'search', 'https://google.com/search'],
    ['https://google.com/', '/search', 'https://google.com/search'],
    ['https://google.com/', 'search', 'https://google.com/search'],
    ['https://google.com//', '//search', 'https://google.com/search'],
    ['', 'google.com/search', '/google.com/search'],
]);

test('join throws when endpoint is an absolute URL to prevent SSRF and credential leakage', function () {
    $trustedBaseUrl = 'https://api.trusted.com';
    $attackerUrl = 'https://attacker.example.com/steal';

    expect(fn () => URLHelper::join($trustedBaseUrl, $attackerUrl))
        ->toThrow(InvalidArgumentException::class, 'Absolute URLs are not allowed in the endpoint');
});

test('creating a pending request with a request that returns absolute URL from resolveEndpoint throws', function () {
    $connector = new TestConnector('https://api.trusted.com');
    $request = new AbsoluteEndpointRequest('https://attacker.example.com/callback');

    expect(fn () => $connector->createPendingRequest($request))
        ->toThrow(InvalidArgumentException::class, 'Absolute URLs are not allowed in the endpoint');
});

test('the URL helper can parse a variety of query parameters', function (string $query, array $expected) {
    expect(URLHelper::parseQueryString($query))->toBe($expected);
})->with([
    ['foo=bar', ['foo' => 'bar']],
    ['foo=bar&name=sam', ['foo' => 'bar', 'name' => 'sam']],
    ['foo==bar&name=sam', ['foo' => 'bar', 'name' => 'sam']],
    ['=abc&name=sam', ['name' => 'sam']],
    ['foo&name=sam', ['foo' => '', 'name' => 'sam']],
    ['account.id=1', ['account.id' => '1']],
    ['name=cowboy%20sam', ['name' => 'cowboy sam']],
    ['name=sam&', ['name' => 'sam']],
]);

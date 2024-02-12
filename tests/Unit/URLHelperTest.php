<?php

declare(strict_types=1);

use Saloon\Helpers\URLHelper;

test('the URL helper will join two URLs together', function ($baseUrl, $endpoint, $expected) {
    expect(URLHelper::join($baseUrl, $endpoint))->toBe($expected);
})->with([
    ['https://google.com', '/search', 'https://google.com/search'],
    ['https://google.com', 'search', 'https://google.com/search'],
    ['https://google.com/', '/search', 'https://google.com/search'],
    ['https://google.com/', 'search', 'https://google.com/search'],
    ['https://google.com//', '//search', 'https://google.com/search'],
    ['', 'https://google.com/search', 'https://google.com/search'],
    ['', 'google.com/search', '/google.com/search'],
    ['https://google.com', 'https://api.google.com/search', 'https://api.google.com/search'],
]);

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

<?php declare(strict_types=1);

use Sammyjo20\Saloon\Helpers\URLHelper;

test('the URL helper will join two URLs together', function ($baseUrl, $endpoint, $expected) {
    expect(URLHelper::join($baseUrl, $endpoint))->toEqual($expected);
})->with([
    ['https://google.com', '/search', 'https://google.com/search'],
    ['https://google.com', 'search', 'https://google.com/search'],
    ['https://google.com/', '/search', 'https://google.com/search'],
    ['https://google.com/', 'search', 'https://google.com/search'],
    ['https://google.com//', '//search', 'https://google.com/search'],
    ['', 'https://google.com/search', 'https://google.com/search'],
    ['', 'google.com/search', '/google.com/search'],
]);

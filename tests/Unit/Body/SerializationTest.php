<?php

declare(strict_types=1);

use Saloon\Data\MultipartValue;
use Saloon\Repositories\Body\FormBodyRepository;
use Saloon\Repositories\Body\JsonBodyRepository;
use Saloon\Repositories\Body\StringBodyRepository;
use Saloon\Exceptions\UnableToCastToStringException;
use Saloon\Repositories\Body\MultipartBodyRepository;

test('the JsonBodyRepository can be encoded into JSON', function () {
    $body = new JsonBodyRepository([
        'name' => 'Sam',
        'sidekick' => 'Mantas',
    ]);

    expect((string)$body)->toEqual('{"name":"Sam","sidekick":"Mantas"}');
});

test('the FormBodyRepository can be encoded into a query list', function () {
    $body = new FormBodyRepository([
        'name' => 'Sam',
        'sidekick' => 'Mantas',
    ]);

    expect((string)$body)->toEqual('name=Sam&sidekick=Mantas');
});

test('the StringBodyRepository can be encoded into a string', function () {
    $body = new StringBodyRepository('name: Sam');

    expect((string)$body)->toEqual('name: Sam');
});

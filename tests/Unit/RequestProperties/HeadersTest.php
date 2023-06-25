<?php

declare(strict_types=1);

use Saloon\Repositories\ArrayStore;
use Saloon\Tests\Fixtures\Requests\HeaderRequest;
use Saloon\Tests\Fixtures\Connectors\HeaderConnector;

test('default headers are merged in from a request', function () {
    $request = new HeaderRequest();

    $headers = $request->headers();

    expect($headers)->toBeInstanceOf(ArrayStore::class);
    expect($headers)->toEqual(new ArrayStore(['X-Custom-Header' => 'Howdy']));
});

test('headers can be managed on a request', function () {
    $request = new HeaderRequest();

    $headers = $request->headers()->add('Content-Type', 'custom/saloon');

    expect($headers)->toBeInstanceOf(ArrayStore::class);

    $headers = $request->headers()->merge(['X-Merge-A' => 'Hello', 'Complex' => ['A', 'B']], ['X-Merge-B' => 'Goodbye', 'Content-Type' => 'overwritten']);

    expect($headers)->toBeInstanceOf(ArrayStore::class);

    $headers = $request->headers()->remove('X-Merge-B');

    expect($headers)->toBeInstanceOf(ArrayStore::class);

    expect($headers->all())->toEqual([
        'X-Custom-Header' => 'Howdy',
        'Content-Type' => 'overwritten',
        'X-Merge-A' => 'Hello',
        'Complex' => ['A', 'B'],
    ]);

    expect($headers->get('X-Custom-Header'))->toEqual('Howdy');
    expect($headers->get('Complex'))->toEqual(['A', 'B']);

    $headers = $request->headers()->set(['X-Different' => 'Yo']);

    expect($headers)->toBeInstanceOf(ArrayStore::class);

    expect($request->headers()->all())->toEqual(['X-Different' => 'Yo']);

    expect($request->headers()->isEmpty())->toBeFalse();
    expect($request->headers()->isNotEmpty())->toBeTrue();
});

test('headers can be managed on a connector', function () {
    $connector = new HeaderConnector();

    $headers = $connector->headers()->add('Content-Type', 'custom/saloon');

    expect($headers)->toBeInstanceOf(ArrayStore::class);

    $headers = $connector->headers()->merge(['X-Merge-A' => 'Hello', 'Complex' => ['A', 'B']], ['X-Merge-B' => 'Goodbye', 'Content-Type' => 'overwritten']);

    expect($headers)->toBeInstanceOf(ArrayStore::class);

    $headers = $connector->headers()->remove('X-Merge-B');

    expect($headers)->toBeInstanceOf(ArrayStore::class);

    expect($headers->all())->toEqual([
        'X-Connector-Header' => 'Sam',
        'Content-Type' => 'overwritten',
        'X-Merge-A' => 'Hello',
        'Complex' => ['A', 'B'],
    ]);

    expect($headers->get('X-Connector-Header'))->toEqual('Sam');
    expect($headers->get('Complex'))->toEqual(['A', 'B']);

    $headers = $connector->headers()->set(['X-Different' => 'Yo']);

    expect($headers)->toBeInstanceOf(ArrayStore::class);

    expect($connector->headers()->all())->toEqual(['X-Different' => 'Yo']);

    expect($connector->headers()->isEmpty())->toBeFalse();
    expect($connector->headers()->isNotEmpty())->toBeTrue();
});

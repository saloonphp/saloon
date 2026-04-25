<?php

declare(strict_types=1);

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Response;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Tests\Fixtures\Requests\UserRequest;

/**
 * Regression for https://github.com/saloonphp/saloon/issues/524
 *
 * Shallow-cloned requests must not share ArrayStore / pipeline instances when those
 * were initialized before cloning (e.g. async paginated pools).
 */
test('cloning a request after query() is initialized gives independent query bags', function () {
    $original = new UserRequest;
    $original->query()->add('key', 'value');

    $a = clone $original;
    $b = clone $original;

    $a->query()->add('page', 1);
    $b->query()->add('page', 2);

    expect($a->query()->get('page'))->toBe(1);
    expect($b->query()->get('page'))->toBe(2);
    expect($original->query()->get('page'))->toBeNull();
    expect($original->query()->get('key'))->toBe('value');
});

test('cloning a request after headers config and delay are initialized gives independent stores', function () {
    $original = new UserRequest;
    $original->headers()->add('X-Test', 'one');
    $original->config()->add('timeout', 10);
    $original->delay()->set(5);

    $clone = clone $original;

    $clone->headers()->add('X-Test', 'two');
    $clone->config()->add('timeout', 20);
    $clone->delay()->set(15);

    expect($original->headers()->get('X-Test'))->toBe('one');
    expect($clone->headers()->get('X-Test'))->toBe('two');
    expect($original->config()->get('timeout'))->toBe(10);
    expect($clone->config()->get('timeout'))->toBe(20);
    expect($original->delay()->get())->toBe(5);
    expect($clone->delay()->get())->toBe(15);
});

test('cloning a request after middleware is initialized gives independent pipelines', function () {
    $original = new UserRequest;
    $original->middleware()->onResponse(static fn (Response $response): Response => $response);

    $clone = clone $original;

    expect($original->middleware())->not->toBe($clone->middleware());
});

test('concurrent pool sends with cloned requests do not share query mutation', function () {
    $sequence = [];
    for ($i = 0; $i < 10; $i++) {
        $sequence[] = MockResponse::make(['ok' => true]);
    }

    $connector = new TestConnector;
    $connector->withMockClient(new MockClient($sequence));

    $base = new UserRequest;
    $base->query()->add('key', 'value');

    $requests = [];
    for ($i = 1; $i <= 10; $i++) {
        $r = clone $base;
        $r->query()->add('page', $i);
        $requests[] = $r;
    }

    $pagesSeen = [];

    $pool = $connector->pool($requests, 5);
    $pool->withResponseHandler(function (Response $response) use (&$pagesSeen): void {
        $pagesSeen[] = (int) $response->getRequest()->query()->get('page');
    });

    $pool->send()->wait();

    expect($pagesSeen)->toHaveCount(10);
    expect(array_unique($pagesSeen))->toHaveCount(10);
});

<?php

declare(strict_types=1);

use Saloon\Contracts\Connector;
use Saloon\Contracts\Request;
use Saloon\Contracts\Response;
use GuzzleHttp\Promise\PromiseInterface;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Tests\Fixtures\Connectors\PagePaginatorConnector;
use Saloon\Tests\Fixtures\Requests\PageGetSuperHeroesRequest;
use Saloon\Tests\Fixtures\Connectors\CursorPaginatorConnector;
use Saloon\Tests\Fixtures\Connectors\OffsetPaginatorConnector;
use Saloon\Tests\Fixtures\Connectors\MinimalPaginatorConnector;
use Saloon\Tests\Fixtures\Requests\CursorGetSuperHeroesRequest;
use Saloon\Tests\Fixtures\Requests\OffsetGetSuperHeroesRequest;

test('you can configure a page paginator, and iterate over every request/response', function (): void {
    $connector = new PagePaginatorConnector;
    $request = new PageGetSuperHeroesRequest;

    $paginator = $connector->paginate($request);

    $responses = [];
    $superheroes = [];

    foreach ($paginator as $response) {
        $responses[] = $response;
        $superheroes = [...$superheroes, ...$response->json('data')];
    }

    expect($paginator->key())->toEqual(5);
    expect($responses)->toHaveCount(4)->each->toBeInstanceOf(Response::class);
    expect($superheroes)->toHaveCount(20)->each->toBeArray();
    expect($superheroes)->toEqual(superheroes());
    expect($paginator->count())->toEqual(4);
});

test('you can configure a offset paginator, and iterate over every request/response', function (): void {
    $connector = new OffsetPaginatorConnector;
    $request = new OffsetGetSuperHeroesRequest;

    $paginator = $connector->paginate($request);

    $responses = [];
    $superheroes = [];

    foreach ($paginator as $response) {
        $responses[] = $response;
        $superheroes = [...$superheroes, ...$response->json('data')];
    }

    expect($paginator->key())->toEqual(20);
    expect($responses)->toHaveCount(4)->each->toBeInstanceOf(Response::class);
    expect($superheroes)->toHaveCount(20)->each->toBeArray();
    expect($superheroes)->toEqual(superheroes());
    expect($paginator->count())->toEqual(4);

    // Test resetting the paginator

    foreach ($paginator as $response) {
        $responses[] = $response;
        $superheroes = [...$superheroes, ...$response->json('data')];
    }

    expect($paginator->key())->toEqual(20);
    expect($responses)->toHaveCount(8)->each->toBeInstanceOf(Response::class);
    expect($superheroes)->toHaveCount(40)->each->toBeArray();
});

test('you can configure a cursor paginator, and iterate over every request/response', function (): void {
    $connector = new CursorPaginatorConnector;
    $request = new CursorGetSuperHeroesRequest;

    $paginator = $connector->paginate($request, limit: 5);

    $responses = [];
    $superheroes = [];

    foreach ($paginator as $response) {
        $responses[] = $response;
        $superheroes = [...$superheroes, ...$response->json('data')];
    }

    expect($paginator->key())->toEqual(5);
    expect($responses)->toHaveCount(4)->each->toBeInstanceOf(Response::class);
    expect($superheroes)->toHaveCount(20)->each->toBeArray();
    expect($superheroes)->toEqual(superheroes());

    // Test resetting the paginator

    foreach ($paginator as $response) {
        $responses[] = $response;
        $superheroes = [...$superheroes, ...$response->json('data')];
    }

    expect($paginator->key())->toEqual(5);
    expect($responses)->toHaveCount(8)->each->toBeInstanceOf(Response::class);
    expect($superheroes)->toHaveCount(40)->each->toBeArray();
});


test('you can configure a minimal paginator, and iterate over every request/response', function (): void {
    $connector = new MinimalPaginatorConnector;
    $request = new PageGetSuperHeroesRequest;

    $paginator = $connector->paginate($request);

    $responses = [];
    $superheroes = [];

    foreach ($paginator as $response) {
        $responses[] = $response;
        $superheroes = [...$superheroes, ...$response->json('data')];
    }

    expect($responses)->toHaveCount(4)->each->toBeInstanceOf(Response::class);
    expect($superheroes)->toHaveCount(20)->each->toBeArray();
    expect($superheroes)->toEqual(superheroes());
});

test('you can iterate a paginator asynchronously', function (Connector $connector, Request $request): void {
    $paginator = $connector->paginate($request);
    $paginator->async();

    // Keep excessively checking between steps.
    expect($paginator->isAsync())->toBeTrue();

    $promises = [];
    $responses = [];
    $superheroes = [];

    foreach ($paginator as $promise) {
        $promises[] = $promise;
        $responses[] = $response = $promise->wait();
        $superheroes = [...$superheroes, ...$response->json('data')];
    }

    expect($paginator->isAsync())->toBeTrue()
        ->and($promises)->toHaveCount(4)->each->toBeInstanceOf(PromiseInterface::class)
        ->and($responses)->toHaveCount(4)->each->toBeInstanceOf(Response::class)
        ->and($superheroes)->toHaveCount(20)->each->toBeArray();

    expect(collect($superheroes)->sortBy('id')->toArray())->toEqual(superheroes());

    expect($paginator->count())->toEqual(4);
})->with([
    [new PagePaginatorConnector, new PageGetSuperHeroesRequest],
    [new OffsetPaginatorConnector, new OffsetGetSuperHeroesRequest],
]);

test('you can iterate asynchronously with a cursor paginator', function () {
    $mockClient = new MockClient([
        new MockResponse([
            'data' => [
                ['name' => 'Sam'],
                ['name' => 'Gareth'],
                ['name' => 'Michael'],
            ],
            'next_page_url' => 'example?cursor=abc',
            'total' => 6,
            'per_page' => 3,
        ]),
        new MockResponse([
            'data' => [
                ['name' => 'Mantas'],
                ['name' => 'Teo'],
                ['name' => 'Patrick'],
            ],
            'next_page_url' => 'example?cursor=def',
            'total' => 6,
            'per_page' => 3,
        ]),
    ]);

    $connector = new CursorPaginatorConnector;
    $connector->withMockClient($mockClient);

    $request = new CursorGetSuperHeroesRequest;

    $paginator = $connector->paginate($request);
    $paginator->async();

    expect($paginator->isAsync())->toBeTrue();

    $promises = [];
    $responses = [];
    $people = [];

    foreach ($paginator as $promise) {
        $promises[] = $promise;
        $responses[] = $response = $promise->wait();
        $people = [...$people, ...$response->json('data')];
    }

    expect($paginator->isAsync())->toBeTrue()
        ->and($promises)->toHaveCount(2)->each->toBeInstanceOf(PromiseInterface::class)
        ->and($responses)->toHaveCount(2)->each->toBeInstanceOf(Response::class)
        ->and($people)->toHaveCount(6)->each->toBeArray();

    expect(collect($people)->sortBy('name')->pluck('name')->toArray())->toEqual([
        'Gareth',
        'Mantas',
        'Michael',
        'Patrick',
        'Sam',
        'Teo',
    ]);

    expect($paginator->count())->toEqual(2);
});

test('you can pool a paginator', function (): void {
    $connector = new PagePaginatorConnector;
    $request = new PageGetSuperHeroesRequest;

    $responses = [];
    $superheroes = [];

    $connector
        ->paginate($request)
        ->pool(concurrency: 2, responseHandler: function (Response $response) use (&$responses, &$superheroes): void {
            $responses[] = $response;
            $superheroes = [...$superheroes, ...$response->json('data')];
        })
        ->send()
        ->wait();

    expect($responses)->toHaveCount(4)->each->toBeInstanceOf(Response::class)
        ->and($superheroes)->toHaveCount(20)->each->toBeArray();

    expect(collect($superheroes)->sortBy('id')->values()->toArray())->toEqual(superheroes());
});

test('you can configure the limit for each paginator and it will send the correct query parameters', function (Connector $connector, Request $request, string $limitKeyName) {
    $paginator = $connector->paginate($request)->setLimit(10)->setLimitKeyName($limitKeyName);

    $responses = [];
    $superheroes = [];

    foreach ($paginator as $response) {
        $responses[] = $response;
        $superheroes = [...$superheroes, ...$response->json('data')];
    }

    // We'll assert that we sent the proper query parameter to the API

    expect(
        array_map(fn($response) => $response->getPendingRequest()->query()->all(), $responses)
    )->each->toHaveKey($limitKeyName, 10);

    expect($responses)->toHaveCount(2)->each->toBeInstanceOf(Response::class);
    expect($superheroes)->toHaveCount(20)->each->toBeArray();
    expect($superheroes)->toEqual(superheroes());
})->with([
    [new PagePaginatorConnector, new PageGetSuperHeroesRequest, 'per_page'],
    [new OffsetPaginatorConnector, new OffsetGetSuperHeroesRequest, 'limit'],
    [new CursorPaginatorConnector, new CursorGetSuperHeroesRequest, 'per_page'],
]);

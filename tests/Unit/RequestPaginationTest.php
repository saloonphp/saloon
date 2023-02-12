<?php

declare(strict_types=1);

namespace Saloon\Tests\Unit;

use Saloon\Contracts\Response;
use Illuminate\Support\LazyCollection;
use GuzzleHttp\Promise\PromiseInterface;
use Saloon\Tests\Fixtures\Connectors\PagePaginatorConnector;
use Saloon\Tests\Fixtures\Requests\PageGetSuperHeroesRequest;

test('you can yield from a paginator', function (): void {
    $connector = new PagePaginatorConnector;
    $request = new PageGetSuperHeroesRequest;

    $responses = [];
    $superheroes = [];

    LazyCollection::make($connector->paginate($request))
        ->each(function (Response $response) use (&$responses, &$superheroes): void {
            $responses[] = $response;
            $superheroes = [...$superheroes, ...$response->json('data')];
        });

    expect($responses)->toHaveCount(4)->each->toBeInstanceOf(Response::class)
        ->and($superheroes)->toHaveCount(20)->each->toBeArray();
});

test('you can collect a paginator', function (): void {
    $connector = new PagePaginatorConnector;
    $request = new PageGetSuperHeroesRequest;

    $collection = $connector->paginate($request)->collect();

    expect($collection)->toBeInstanceOf(LazyCollection::class);

    $superheroes = [];

    // TODO: This is definitely not the right way.
    //       Make proper assertions.
    $responses = $collection->each(function (Response $response) use (&$superheroes): void {
        $superheroes = [...$superheroes, ...$response->json('data')];
    })->all();

    expect($responses)->toHaveCount(4)->each->toBeInstanceOf(Response::class)
        ->and($superheroes)->toHaveCount(20)->each->toBeArray();
});

test('you can continue a paginator from where it left off', function (): void {
    $connector = new PagePaginatorConnector;
    $request = new PageGetSuperHeroesRequest;

    $paginator = $connector->paginate($request);

    // Ensure it's disabled.
    $paginator->enableRewinding(false);

    $responses = [];
    $superheroes = [];

    foreach ($paginator as $response) {
        $responses[] = $response;
        $superheroes = [...$superheroes, ...$response->json('data')];

        // Skip out of the first loop, to ensure in the second, that the RequestPaginator isn't reset.
        break;
    }

    expect($responses)->toHaveCount(1)->each->toBeInstanceOf(Response::class)
        ->and($superheroes)->toHaveCount(5)->each->toBeArray();

    foreach ($paginator as $response) {
        $responses[] = $response;
        $superheroes = [...$superheroes, ...$response->json('data')];
    }

    expect($responses)->toHaveCount(4)->each->toBeInstanceOf(Response::class)
        ->and($superheroes)->toHaveCount(20)->each->toBeArray();
});

test('you can iterate a paginator in for loops', function (): void {
    $connector = new PagePaginatorConnector;
    $request = new PageGetSuperHeroesRequest;

    $paginator = $connector->paginate($request);

    $responses = [];
    $superheroes = [];

    for (; $paginator->valid(); $paginator->next()) {
        $responses[] = $response = $paginator->current();
        $superheroes = [...$superheroes, ...$response->json('data')];
    }

    expect($responses)->toHaveCount(4)->each->toBeInstanceOf(Response::class)
        ->and($superheroes)->toHaveCount(20)->each->toBeArray();
});

test('you can automagically rewind a paginator, starting over from the start in a new loop', function (): void {
    $connector = new PagePaginatorConnector;
    $request = new PageGetSuperHeroesRequest;

    $paginator = $connector->paginate($request);
    $paginator->enableRewinding();

    $responses = [];
    $superheroes = [];

    foreach ($paginator as $response) {
        $responses[] = $response;
        $superheroes = [...$superheroes, ...$response->json('data')];

        break;
    }

    expect($responses)->toHaveCount(1)->each->toBeInstanceOf(Response::class)
        ->and($superheroes)->toHaveCount(5)->each->toBeArray();

    foreach ($paginator as $response) {
        $responses[] = $response;
        $superheroes = [...$superheroes, ...$response->json('data')];
    }

    expect($responses)->toHaveCount(5)->each->toBeInstanceOf(Response::class)
        ->and($superheroes)->toHaveCount(25)->each->toBeArray();
});

test('you can manually rewind a paginator, starting over from the start', function (): void {
    $connector = new PagePaginatorConnector;
    $request = new PageGetSuperHeroesRequest;

    $paginator = $connector->paginate($request);
    $paginator->enableRewinding();

    // Keep excessively checking between steps.
    expect($paginator->shouldRewind())->toBeTrue();

    $responses = [];
    $superheroes = [];

    foreach ($paginator as $response) {
        $responses[] = $response;
        $superheroes = [...$superheroes, ...$response->json('data')];

        break;
    }

    expect($paginator->shouldRewind())->toBeTrue()
        ->and($responses)->toHaveCount(1)->each->toBeInstanceOf(Response::class)
        ->and($superheroes)->toHaveCount(5)->each->toBeArray();

    // Also using a for-loop to ensure that PHP itself doesn't automagically rewind it.
    for ($paginator->rewind(); $paginator->valid(); $paginator->next()) {
        $responses[] = $response = $paginator->current();
        $superheroes = [...$superheroes, ...$response->json('data')];
    }

    expect($paginator->shouldRewind())->toBeTrue()
        ->and($responses)->toHaveCount(5)->each->toBeInstanceOf(Response::class)
        ->and($superheroes)->toHaveCount(25)->each->toBeArray();
});

test('you can iterate a paginator asynchronously', function (): void {
    $connector = new PagePaginatorConnector;
    $request = new PageGetSuperHeroesRequest;

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
});

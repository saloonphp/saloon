<?php

declare(strict_types=1);

use GuzzleHttp\Promise\PromiseInterface;
use Saloon\Contracts\Response;
use Saloon\Tests\Fixtures\Connectors\PagePaginatorConnector;
use Saloon\Tests\Fixtures\Requests\PageGetSuperHeroesRequest;
use Saloon\Tests\Fixtures\Connectors\OffsetPaginatorConnector;
use Saloon\Tests\Fixtures\Connectors\MinimalPaginatorConnector;
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

    expect($responses)->toHaveCount(4)->each->toBeInstanceOf(Response::class)
        ->and($superheroes)->toHaveCount(20)->each->toBeArray();
});

test('you can configure a offset paginator, and iterate over every request/response', function (): void {
    $connector = new OffsetPaginatorConnector;
    $request = new OffsetGetSuperHeroesRequest;

    $paginator = $connector->paginate($request, limit: 5);

    $responses = [];
    $superheroes = [];

    foreach ($paginator as $response) {
        $responses[] = $response;
        $superheroes = [...$superheroes, ...$response->json('data')];
    }

    expect($responses)->toHaveCount(4)->each->toBeInstanceOf(Response::class)
        ->and($superheroes)->toHaveCount(20)->each->toBeArray();
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

    expect($responses)->toHaveCount(4)->each->toBeInstanceOf(Response::class)
        ->and($superheroes)->toHaveCount(20)->each->toBeArray();
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

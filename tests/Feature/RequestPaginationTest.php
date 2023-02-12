<?php

declare(strict_types=1);

use Saloon\Contracts\Response;
use Saloon\Tests\Fixtures\Connectors\MinimalPaginatorConnector;
use Saloon\Tests\Fixtures\Connectors\PagePaginatorConnector;
use Saloon\Tests\Fixtures\Requests\PageGetSuperHeroesRequest;
use Saloon\Tests\Fixtures\Connectors\OffsetPaginatorConnector;
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

<?php

declare(strict_types=1);

use GuzzleHttp\Promise\PromiseInterface;
use Illuminate\Support\LazyCollection;
use Saloon\Contracts\Response;
use Saloon\Tests\Fixtures\Connectors\OffsetRequestPaginatorConnector;
use Saloon\Tests\Fixtures\Connectors\PageRequestPaginatorConnector;
use Saloon\Tests\Fixtures\Requests\OffsetGetSuperHeroesRequest;
use Saloon\Tests\Fixtures\Requests\PageGetSuperHeroesRequest;

test('you can configure a page paginator, and iterate over every request/response', function (): void {
    $connector = new PageRequestPaginatorConnector;
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
    $connector = new OffsetRequestPaginatorConnector;
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

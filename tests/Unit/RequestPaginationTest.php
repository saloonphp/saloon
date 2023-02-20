<?php

declare(strict_types=1);

namespace Saloon\Tests\Unit;

use Saloon\Contracts\Response;
use Illuminate\Support\Collection;
use Illuminate\Support\LazyCollection;
use Saloon\Exceptions\PaginatorException;
use Saloon\Tests\Fixtures\Connectors\PagePaginatorConnector;
use Saloon\Tests\Fixtures\Requests\PageGetSuperHeroesRequest;
use Saloon\Tests\Fixtures\Connectors\CursorPaginatorConnector;
use Saloon\Tests\Fixtures\Connectors\OffsetPaginatorConnector;
use Saloon\Tests\Fixtures\Requests\CursorGetSuperHeroesRequest;
use Saloon\Tests\Fixtures\Requests\OffsetGetSuperHeroesRequest;

test('you can yield from a paginator', function (): void {
    $mockClient = paginationMockClient('pagination/per-page');

    $connector = new PagePaginatorConnector;
    $connector->withMockClient($mockClient);

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
    $mockClient = paginationMockClient('pagination/per-page');

    $connector = new PagePaginatorConnector;
    $connector->withMockClient($mockClient);

    $request = new PageGetSuperHeroesRequest;

    $collection = $connector->paginate($request)->collect();

    expect($collection)->toBeInstanceOf(LazyCollection::class);

    $superheroes = [];

    $responses = $collection->each(function (Response $response) use (&$superheroes): void {
        $superheroes = [...$superheroes, ...$response->json('data')];
    })->all();

    expect($responses)->toHaveCount(4)->each->toBeInstanceOf(Response::class)
        ->and($superheroes)->toHaveCount(20)->each->toBeArray();
});

test('you can collect a paginator with a regular collection', function (): void {
    $mockClient = paginationMockClient('pagination/per-page');

    $connector = new PagePaginatorConnector;
    $connector->withMockClient($mockClient);

    $request = new PageGetSuperHeroesRequest;

    $collection = $connector->paginate($request)->collect(lazy: false);

    expect($collection)->toBeInstanceOf(Collection::class);

    $superheroes = [];

    $responses = $collection->each(function (Response $response) use (&$superheroes): void {
        $superheroes = [...$superheroes, ...$response->json('data')];
    })->all();

    expect($responses)->toHaveCount(4)->each->toBeInstanceOf(Response::class)
        ->and($superheroes)->toHaveCount(20)->each->toBeArray();
});

test('you can collect a paginator with a key to yield results', function (): void {
    $mockClient = paginationMockClient('pagination/per-page');

    $connector = new PagePaginatorConnector;
    $connector->withMockClient($mockClient);

    $request = new PageGetSuperHeroesRequest;

    $collection = $connector->paginate($request)->collect('data');

    expect($collection)->toBeInstanceOf(LazyCollection::class);

    $superheroes = [];

    $responses = $collection->each(function (array $data) use (&$superheroes): void {
        $superheroes[] = $data;
    })->all();

    expect($responses)->toHaveCount(20)->each->toBeArray()
        ->and($superheroes)->toHaveCount(20)->each->toBeArray();
});

test('you can collect a paginator with a key to yield results without collapsing', function (): void {
    $mockClient = paginationMockClient('pagination/per-page');

    $connector = new PagePaginatorConnector;
    $connector->withMockClient($mockClient);

    $request = new PageGetSuperHeroesRequest;

    $collection = $connector->paginate($request)->collect('data', collapse: false);

    expect($collection)->toBeInstanceOf(LazyCollection::class);

    $superheroes = [];

    $responses = $collection->each(function (array $data) use (&$superheroes): void {
        $superheroes = array_merge($superheroes, $data);
    })->all();

    expect($responses)->toHaveCount(4)->each->toBeArray()
        ->and($superheroes)->toHaveCount(20)->each->toBeArray();
});

test('you can continue a paginator from where it left off', function (): void {
    $mockClient = paginationMockClient('pagination/per-page');

    $connector = new PagePaginatorConnector;
    $connector->withMockClient($mockClient);

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
    $mockClient = paginationMockClient('pagination/per-page');

    $connector = new PagePaginatorConnector;
    $connector->withMockClient($mockClient);

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
    $mockClient = paginationMockClient('pagination/per-page');

    $connector = new PagePaginatorConnector;
    $connector->withMockClient($mockClient);

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
    $mockClient = paginationMockClient('pagination/per-page');

    $connector = new PagePaginatorConnector;
    $connector->withMockClient($mockClient);

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

test('you can query the total results of a paginator before it iterated', function () {
    $mockClient = paginationMockClient('pagination/per-page');
    $connector = new PagePaginatorConnector;
    $connector->withMockClient($mockClient);

    $request = new PageGetSuperHeroesRequest;
    $paginator = $connector->paginate($request);

    expect($paginator->totalResults())->toEqual(20);
    expect($paginator->totalPages())->toEqual(4);
});

test('if it cannot calculate the total pages it will throw an exception', function () {
    $connector = new CursorPaginatorConnector;

    $request = new PageGetSuperHeroesRequest;
    $paginator = $connector->paginate($request)->setTotalKeyName('count');

    $this->expectException(PaginatorException::class);
    $this->expectExceptionMessage('Unable to calculate the total results from the response. Make sure the total key is correct.');

    expect($paginator->totalResults())->toEqual(20);
});

test('you can set and get the limit and total key names', function () {
    $connector = new CursorPaginatorConnector;
    $request = new PageGetSuperHeroesRequest;
    $paginator = $connector->paginate($request);

    expect($paginator->getLimitKeyName())->toEqual('limit');
    expect($paginator->getTotalKeyName())->toEqual('total');

    $paginator->setLimitKeyName('top');
    $paginator->setTotalKeyName('count');

    expect($paginator->getLimitKeyName())->toEqual('top');
    expect($paginator->getTotalKeyName())->toEqual('count');
});

test('on a paged paginator you can set and get the current page, page key and next page key', function () {
    $connector = new PagePaginatorConnector;
    $request = new PageGetSuperHeroesRequest;
    $paginator = $connector->paginate($request);

    expect($paginator->getCurrentPage())->toEqual(1);
    expect($paginator->getPageKeyName())->toEqual('page');
    expect($paginator->getNextPageKeyName())->toEqual('next_page_url');

    $paginator->setCurrentPage(2);
    $paginator->setPageKeyName('paper');
    $paginator->setNextPageKeyName('next');

    expect($paginator->getCurrentPage())->toEqual(2);
    expect($paginator->getPageKeyName())->toEqual('paper');
    expect($paginator->getNextPageKeyName())->toEqual('next');
});

test('with a offset paginator you can set and get the offset key name', function () {
    $connector = new OffsetPaginatorConnector;
    $request = new OffsetGetSuperHeroesRequest;
    $paginator = $connector->paginate($request);

    expect($paginator->getOffsetKeyName())->toEqual('offset');

    $paginator->setOffsetKeyName('skip');

    expect($paginator->getOffsetKeyName())->toEqual('skip');
});

test('with a cursor paginator you can set and get the next page key and cursor key', function () {
    $connector = new CursorPaginatorConnector;
    $request = new CursorGetSuperHeroesRequest;
    $paginator = $connector->paginate($request);

    expect($paginator->getNextPageKeyName())->toEqual('next_page_url');
    expect($paginator->getCursorKeyName())->toEqual('cursor');

    $paginator->setNextPageKeyName('next');
    $paginator->setCursorKeyName('token');

    expect($paginator->getNextPageKeyName())->toEqual('next');
    expect($paginator->getCursorKeyName())->toEqual('token');
});

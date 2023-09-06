<?php

declare(strict_types=1);

use Saloon\Repositories\ArrayStore;
use Saloon\Tests\Fixtures\Requests\QueryParameterRequest;
use Saloon\Tests\Fixtures\Connectors\QueryParameterConnector;

test('default query parameters are merged in from a request', function () {
    $request = new QueryParameterRequest();

    $query = $request->query();

    expect($query)->toBeInstanceOf(ArrayStore::class);
    expect($query)->toEqual(new ArrayStore(['per_page' => 100]));
});

test('query parameters can be managed on a request', function () {
    $request = new QueryParameterRequest();

    $query = $request->query()->add('page', 1);

    expect($query)->toBeInstanceOf(ArrayStore::class);

    $query = $request->query()->merge(['search' => 'Sam', 'category' => 'Cowboy'], ['per_page' => 200]);

    expect($query)->toBeInstanceOf(ArrayStore::class);

    $query = $request->query()->remove('category');

    expect($query)->toBeInstanceOf(ArrayStore::class);

    expect($query->all())->toEqual([
        'per_page' => 200,
        'page' => 1,
        'search' => 'Sam',
    ]);

    expect($query->get('page'))->toEqual(1);

    $query = $request->query()->set(['debug' => true]);

    expect($query)->toBeInstanceOf(ArrayStore::class);

    expect($request->query()->all())->toEqual(['debug' => true]);

    expect($request->query()->isEmpty())->toBeFalse();
    expect($request->query()->isNotEmpty())->toBeTrue();
});

test('query parameters can be managed on a connector', function () {
    $connector = new QueryParameterConnector();

    $query = $connector->query()->add('page', 1);

    expect($query)->toBeInstanceOf(ArrayStore::class);

    $query = $connector->query()->merge(['search' => 'Sam', 'category' => 'Cowboy'], ['sort' => 'last_name']);

    expect($query)->toBeInstanceOf(ArrayStore::class);

    $query = $connector->query()->remove('category');

    expect($query)->toBeInstanceOf(ArrayStore::class);

    expect($query->all())->toEqual([
        'sort' => 'last_name',
        'page' => 1,
        'search' => 'Sam',
    ]);

    expect($query->get('page'))->toEqual(1);

    $query = $connector->query()->set(['debug' => true]);

    expect($query)->toBeInstanceOf(ArrayStore::class);

    expect($connector->query()->all())->toEqual(['debug' => true]);

    expect($connector->query()->isEmpty())->toBeFalse();
    expect($connector->query()->isNotEmpty())->toBeTrue();
});

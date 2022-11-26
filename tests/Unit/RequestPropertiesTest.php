<?php

declare(strict_types=1);

use Saloon\Repositories\ArrayStore;
use Saloon\Helpers\MiddlewarePipeline;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Tests\Fixtures\Requests\DefaultPropertiesRequest;

test('you can retrieve all the request parameters methods', function () {
    $request = new UserRequest;

    expect($request->headers())->toBeInstanceOf(ArrayStore::class);
    expect($request->query())->toBeInstanceOf(ArrayStore::class);
    expect($request->config())->toBeInstanceOf(ArrayStore::class);
    expect($request->middleware())->toBeInstanceOf(MiddlewarePipeline::class);
});

test('all of the request properties can have default properties', function () {
    $request = new DefaultPropertiesRequest;

    expect($request->headers())->toEqual(new ArrayStore(['X-Favourite-Artist' => 'Luke Combs']));
    expect($request->query())->toEqual(new ArrayStore(['format' => 'json']));
    expect($request->config())->toEqual(new ArrayStore(['debug' => true]));
});

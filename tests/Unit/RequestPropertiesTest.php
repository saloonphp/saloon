<?php

use Sammyjo20\Saloon\Helpers\MiddlewarePipeline;
use Sammyjo20\Saloon\Repositories\BodyRepository;
use Sammyjo20\Saloon\Repositories\ArrayStore;
use Sammyjo20\Saloon\Tests\Fixtures\Requests\UserRequest;
use Sammyjo20\Saloon\Tests\Fixtures\Requests\DefaultPropertiesRequest;

test('you can retrieve all the request parameters methods', function () {
    $request = new UserRequest;

    expect($request->headers())->toBeInstanceOf(ArrayStore::class);
    expect($request->queryParameters())->toBeInstanceOf(ArrayStore::class);
    expect($request->config())->toBeInstanceOf(ArrayStore::class);
    expect($request->data())->toBeInstanceOf(BodyRepository::class);
    expect($request->middlewarePipeline())->toBeInstanceOf(MiddlewarePipeline::class);
});

test('all of the request properties can have default properties', function () {
    $request = new DefaultPropertiesRequest;

    expect($request->headers())->toEqual(new ArrayStore(['X-Favourite-Artist' => 'Luke Combs']));
    expect($request->queryParameters())->toEqual(new ArrayStore(['format' => 'json']));
    expect($request->data())->toEqual(new BodyRepository(['song' => 'Call Me']));
    expect($request->config())->toEqual(new ArrayStore(['debug' => true]));
});

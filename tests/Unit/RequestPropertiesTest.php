<?php

use Sammyjo20\Saloon\Helpers\MiddlewarePipeline;
use Sammyjo20\Saloon\Repositories\BodyRepository;
use Sammyjo20\Saloon\Repositories\ArrayRepository;
use Sammyjo20\Saloon\Tests\Fixtures\Requests\UserRequest;
use Sammyjo20\Saloon\Tests\Fixtures\Requests\DefaultPropertiesRequest;

test('you can retrieve all the request parameters methods', function () {
    $request = new UserRequest;

    expect($request->headers())->toBeInstanceOf(ArrayRepository::class);
    expect($request->queryParameters())->toBeInstanceOf(ArrayRepository::class);
    expect($request->config())->toBeInstanceOf(ArrayRepository::class);
    expect($request->data())->toBeInstanceOf(BodyRepository::class);
    expect($request->middlewarePipeline())->toBeInstanceOf(MiddlewarePipeline::class);
});

test('all of the request properties can have default properties', function () {
    $request = new DefaultPropertiesRequest;

    expect($request->headers())->toEqual(new ArrayRepository(['X-Favourite-Artist' => 'Luke Combs']));
    expect($request->queryParameters())->toEqual(new ArrayRepository(['format' => 'json']));
    expect($request->data())->toEqual(new BodyRepository(['song' => 'Call Me']));
    expect($request->config())->toEqual(new ArrayRepository(['debug' => true]));
});

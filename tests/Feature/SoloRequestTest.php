<?php

use GuzzleHttp\Promise\PromiseInterface;
use Saloon\Exceptions\RequestException;
use Saloon\Http\Responses\Response;
use Saloon\Tests\Fixtures\Requests\ErrorRequest;
use Saloon\Tests\Fixtures\Requests\SoloErrorRequest;
use Saloon\Tests\Fixtures\Requests\SoloUserRequest;

test('a solo request can be sent synchronously', function () {
    $request = new SoloUserRequest;
    $response = $request->send();

    $data = $response->json();

    expect($response)->toBeInstanceOf(Response::class);
    expect($response->isMocked())->toBeFalse();
    expect($response->status())->toEqual(200);

    expect($data)->toEqual([
        'name' => 'Sammyjo20',
        'actual_name' => 'Sam',
        'twitter' => '@carre_sam',
    ]);
});

test('a synchronous solo request can handle an exception property', function () {
    $request = new SoloErrorRequest();
    $response = $request->send();

    expect($response->isMocked())->toBeFalse();
    expect($response->status())->toEqual(500);
});

test('a solo request can be sent asynchronously', function () {
    $request = new SoloUserRequest;
    $promise = $request->sendAsync();

    expect($promise)->toBeInstanceOf(PromiseInterface::class);

    $response = $promise->wait();

    $data = $response->json();

    expect($response)->toBeInstanceOf(Response::class);
    expect($response->isMocked())->toBeFalse();
    expect($response->status())->toEqual(200);

    expect($data)->toEqual([
        'name' => 'Sammyjo20',
        'actual_name' => 'Sam',
        'twitter' => '@carre_sam',
    ]);
});

test('a asynchronous solo request can handle an exception property', function () {
    $request = new SoloErrorRequest();
    $promise = $request->sendAsync();

    $this->expectException(RequestException::class);

    $promise->wait();
});

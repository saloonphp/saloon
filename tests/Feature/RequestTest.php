<?php

declare(strict_types=1);

use Saloon\Http\Response;
use Saloon\Http\PendingRequest;
use Saloon\Http\Senders\GuzzleSender;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Tests\Fixtures\Requests\ErrorRequest;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Tests\Fixtures\Requests\SoloUserRequest;
use Saloon\Tests\Fixtures\Requests\SoloErrorRequest;
use Saloon\Tests\Fixtures\Requests\HasConnectorUserRequest;

test('a request can be made successfully', function () {
    $connector = new TestConnector();
    $response = $connector->send(new UserRequest);

    $data = $response->json();

    expect($response->getPendingRequest()->isAsynchronous())->toBeFalse();
    expect($response)->toBeInstanceOf(Response::class);
    expect($response->isMocked())->toBeFalse();
    expect($response->status())->toEqual(200);

    expect($data)->toEqual([
        'name' => 'Sammyjo20',
        'actual_name' => 'Sam',
        'twitter' => '@carre_sam',
    ]);
});

test('a request can handle an exception properly', function () {
    $connector = new TestConnector();
    $response = $connector->send(new ErrorRequest);

    expect($response->isMocked())->toBeFalse();
    expect($response->status())->toEqual(500);
});

test('a request with HasConnector can be sent individually', function () {
    $request = new HasConnectorUserRequest();

    expect($request->connector())->toBeInstanceOf(TestConnector::class);
    expect($request->sender())->toBeInstanceOf(GuzzleSender::class);
    expect($request->createPendingRequest())->toBeInstanceOf(PendingRequest::class);

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
    $request = new SoloErrorRequest;
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

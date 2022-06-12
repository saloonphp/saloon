<?php

use GuzzleHttp\Promise\Promise;
use Sammyjo20\Saloon\Clients\MockClient;
use Sammyjo20\Saloon\Http\MockResponse;
use Sammyjo20\Saloon\Http\Responses\SaloonResponse;
use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Tests\Fixtures\Connectors\RequestSelectionConnector;
use Sammyjo20\Saloon\Tests\Fixtures\Connectors\TestConnector;
use Sammyjo20\Saloon\Tests\Fixtures\Requests\UserRequest;

test('a connector class can be instantiated using the make method', function () {
    $connectorA = TestConnector::make();

    expect($connectorA)->toBeInstanceOf(TestConnector::class);

    $connectorB = RequestSelectionConnector::make('yee-haw-1-2-3');

    expect($connectorB)->toBeInstanceOf(RequestSelectionConnector::class);
    expect($connectorB)->apiKey->toEqual('yee-haw-1-2-3');
});

test('you can prepare a request through the connector', function () {
    $connector = new TestConnector();
    $connector->unique = true;

    $request = $connector->send(new UserRequest);

    expect($request)->toBeInstanceOf(SaloonRequest::class);
    expect($request->getConnector())->toEqual($connector);
});

test('you can send a request through the connector', function () {
    $mockClient = new MockClient([
        new MockResponse(['name' => 'Sammyjo20', 'actual_name' => 'Sam Carré', 'twitter' => '@carre_sam']),
    ]);

    $connector = new TestConnector();
    $response = $connector->send(new UserRequest, $mockClient);

    expect($response)->toBeInstanceOf(SaloonResponse::class);
    expect($response->json())->toEqual(['name' => 'Sammyjo20', 'actual_name' => 'Sam Carré', 'twitter' => '@carre_sam']);
});

test('you can send an asynchronous request through the connector', function () {
    $mockClient = new MockClient([
        new MockResponse(['name' => 'Sammyjo20', 'actual_name' => 'Sam Carré', 'twitter' => '@carre_sam']),
    ]);

    $connector = new TestConnector();
    $promise = $connector->sendAsync(new UserRequest, $mockClient);

    expect($promise)->toBeInstanceOf(Promise::class);

    $response = $promise->wait();

    expect($response)->toBeInstanceOf(SaloonResponse::class);
    expect($response->json())->toEqual(['name' => 'Sammyjo20', 'actual_name' => 'Sam Carré', 'twitter' => '@carre_sam']);
});

<?php

use GuzzleHttp\Promise\Utils;
use Sammyjo20\Saloon\Http\MockResponse;
use Sammyjo20\Saloon\Clients\MockClient;
use Sammyjo20\Saloon\Tests\Fixtures\Requests\PostRequest;
use Sammyjo20\Saloon\Tests\Fixtures\Requests\UserRequest;
use Sammyjo20\Saloon\Tests\Fixtures\Requests\ErrorRequest;
use Sammyjo20\Saloon\Tests\Fixtures\Connectors\TestConnector;

test('a request can be made successfully', function () {
    $request = new UserRequest();
    $response = $request->send();
    $data = $response->json();

    expect($response->isMocked())->toBeFalse();
    expect($response->status())->toEqual(200);

    expect($data)->toEqual([
        'name' => 'Sammyjo20',
        'actual_name' => 'Sam',
        'twitter' => '@carre_sam',
    ]);
});

test('(remove) a request can be sent with json data', function () {
    $request = new PostRequest();

    dd($request->body()->all());
});

test('(remove) asynchronous requests work', function () {
    $connector = new TestConnector();

    $responseA = $connector->sendAsync(new UserRequest);
    $responseB = $connector->sendAsync(new UserRequest);
    $responseC = $connector->sendAsync(new UserRequest);

    Utils::unwrap([$responseA, $responseB, $responseC]);

    dd($responseA);
});

test('(remove) fake asynchronous requests work', function () {
    $mockClient = new MockClient([
        MockResponse::make(200, ['name' => 'Sam']),
        MockResponse::make(200, ['name' => 'Charlotte']),
        MockResponse::make(200, ['name' => 'Gareth']),
    ]);

    $connector = new TestConnector();
    $connector->withMockClient($mockClient);

    $responseA = $connector->sendAsync(new UserRequest);
    $responseB = $connector->sendAsync(new UserRequest);
    $responseC = $connector->sendAsync(new UserRequest);

    Utils::unwrap([$responseA, $responseB, $responseC]);

    dd($responseC);
});

test('a request can handle an exception properly', function () {
    $request = new ErrorRequest();
    $response = $request->send();

    expect($response->isMocked())->toBeFalse();
    expect($response->status())->toEqual(500);
});

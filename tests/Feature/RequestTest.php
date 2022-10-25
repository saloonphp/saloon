<?php

use GuzzleHttp\Promise\Utils;
use Sammyjo20\Saloon\Http\Responses\GuzzleResponse;
use Sammyjo20\Saloon\Http\Responses\SaloonResponse;
use Sammyjo20\Saloon\Tests\Fixtures\Connectors\TestConnector;
use Sammyjo20\Saloon\Tests\Fixtures\Requests\UserRequest;
use Sammyjo20\Saloon\Tests\Fixtures\Requests\ErrorRequest;

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

test('asynchronous requests work', function () {
    $connector = new TestConnector();

    $responseA = $connector->sendAsync(new UserRequest);
    $responseB = $connector->sendAsync(new UserRequest);
    $responseC = $connector->sendAsync(new UserRequest);

    Utils::unwrap([$responseA, $responseB, $responseC]);
});

test('a request can handle an exception properly', function () {
    $request = new ErrorRequest();
    $response = $request->send();

    expect($response->isMocked())->toBeFalse();
    expect($response->status())->toEqual(500);
});

<?php

use Sammyjo20\Saloon\Clients\MockClient;
use Sammyjo20\Saloon\Exceptions\SaloonInvalidMockResponseCaptureMethodException;
use Sammyjo20\Saloon\Http\MockResponse;
use Sammyjo20\Saloon\Tests\Resources\Requests\UserRequest;

test('a request can be mocked with a sequence', function () {
    $mockClient = new MockClient([
        new MockResponse(['name' => 'Sam'], 200),
        new MockResponse(['name' => 'Alex'], 200),
        new MockResponse(['error' => 'Server Unavailable'], 500),
    ]);

    $responseA = (new UserRequest)->send($mockClient);

    expect($responseA->isMocked())->toBeTrue();
    expect($responseA->json())->toEqual(['name' => 'Sam']);
    expect($responseA->status())->toEqual(200);

    $responseB = (new UserRequest)->send($mockClient);

    expect($responseB->isMocked())->toBeTrue();
    expect($responseB->json())->toEqual(['name' => 'Alex']);
    expect($responseB->status())->toEqual(200);

    $responseC = (new UserRequest)->send($mockClient);

    expect($responseC->isMocked())->toBeTrue();
    expect($responseC->json())->toEqual(['error' => 'Server Unavailable']);
    expect($responseC->status())->toEqual(500);
});

test('a request can be mocked with a url defined', function () {
    //
});

test('a request can be mocked with a connector defined', function () {
    //
});

test('a request can be mocked with a request defined', function () {
    //
});

<?php

use Sammyjo20\Saloon\Clients\MockClient;
use Sammyjo20\Saloon\Http\MockResponse;

test('pulling a response from the sequence will return the correct response', function () {
    $responseA = new MockResponse;
    $responseB = new MockResponse;
    $responseC = new MockResponse;

    $mockClient = new MockClient([$responseA, $responseB, $responseC]);

    expect($mockClient->getNextResponse())->toBe($responseA);
    expect($mockClient->getNextResponse())->toBe($responseB);
    expect($mockClient->getNextResponse())->toBe($responseC);
});

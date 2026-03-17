<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\Uri;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Tests\Fixtures\Requests\ModifiedPsrUserRequest;
use Saloon\Tests\Fixtures\Connectors\ModifiedPsrRequestConnector;

test('the connector and request can modify the psr request when it is created', function () {
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Sam']),
    ]);

    $connector = new ModifiedPsrRequestConnector;
    $connector->withMockClient($mockClient);

    $response = $connector->send(new ModifiedPsrUserRequest);

    // The connector will change the URI to https://google.com

    expect($response->getPsrRequest()->getUri())->toEqual(new Uri('https://google.com'));

    // The request will add the X-Howdy header

    expect($response->getPsrRequest()->getHeaders())->toHaveKey('X-Howdy', ['Yeehaw']);
});

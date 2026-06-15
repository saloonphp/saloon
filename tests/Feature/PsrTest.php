<?php

declare(strict_types=1);

use GuzzleHttp\Psr7\Uri;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Tests\Fixtures\Requests\ModifiedPsrUserRequest;
use Saloon\Tests\Fixtures\Requests\NullHeaderRequest;
use Saloon\Tests\Fixtures\Connectors\ModifiedPsrRequestConnector;
use Saloon\Tests\Fixtures\Connectors\TestConnector;

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

test('The psr request and readers must be converted to empty string', function () {
    $mockClient = new MockClient([
        MockResponse::make(headers: ['X-Null-Header' => null]),
    ]);

    $connector = new TestConnector()->debug();
    $connector->withMockClient($mockClient);

    $response = $connector->send(new NullHeaderRequest());

    // The request will convert null to empty string
    expect($response->getPsrRequest()->getHeader('X-Null-Header')[0])->toBe('');

    // The responde will convert null header to empty string
    expect($response->headers()->get('X-Null-Header'))->toBe('');
});

<?php

declare(strict_types=1);

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Tests\Fixtures\Connectors\ResourceConnector;

test('a resource can be used to send a request', function () {
    $mockClient = new MockClient([
        MockResponse::fixture('user'),
    ]);

    $connector = new ResourceConnector;
    $connector->withMockClient($mockClient);

    expect($connector->user()->get())->toEqual([
        'name' => 'Sammyjo20',
        'actual_name' => 'Sam',
        'twitter' => '@carre_sam',
    ]);
});

<?php

declare(strict_types=1);

use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Tests\Fixtures\Connectors\RateLimitedConnector;

test('a rate limiter will be increased as you make requests', function () {
    $mockClient = new MockClient([
        new MockResponse(['name' => 'Sammy', 'catchphrase' => 'Yee-haw!']),
    ]);

    $connector = new RateLimitedConnector;
    $connector->withMockClient($mockClient);

    $response = $connector->send(new UserRequest);

    dd($response->json());
});

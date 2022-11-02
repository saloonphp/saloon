<?php declare(strict_types=1);

use GuzzleHttp\Promise\Utils;
use Sammyjo20\Saloon\Http\MockResponse;
use Sammyjo20\Saloon\Clients\MockClient;
use Sammyjo20\Saloon\Http\Responses\PsrResponse;
use Sammyjo20\Saloon\Tests\Fixtures\Requests\PostRequest;
use Sammyjo20\Saloon\Tests\Fixtures\Requests\UserRequest;
use Sammyjo20\Saloon\Tests\Fixtures\Requests\ErrorRequest;
use Sammyjo20\Saloon\Tests\Fixtures\Connectors\TestConnector;

test('a request can be made successfully', function () {
    $request = new UserRequest();
    $response = $request->send();
    $data = $response->json();

    expect($response)->toBeInstanceOf(PsrResponse::class);
    expect($response->isMocked())->toBeFalse();
    expect($response->status())->toEqual(200);

    expect($data)->toEqual([
        'name' => 'Sammyjo20',
        'actual_name' => 'Sam',
        'twitter' => '@carre_sam',
    ]);
});

test('a request can handle an exception properly', function () {
    $request = new ErrorRequest();
    $response = $request->send();

    expect($response->isMocked())->toBeFalse();
    expect($response->status())->toEqual(500);
});

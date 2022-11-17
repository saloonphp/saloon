<?php declare(strict_types=1);

use Saloon\Http\Responses\Response;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Tests\Fixtures\Requests\ErrorRequest;

test('a request can be made successfully', function () {
    $request = new UserRequest();
    $response = $request->send();
    $data = $response->json();

    expect($response)->toBeInstanceOf(Response::class);
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

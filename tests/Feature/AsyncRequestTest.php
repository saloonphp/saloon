<?php declare(strict_types=1);

use Saloon\Contracts\Response;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use GuzzleHttp\Promise\PromiseInterface;
use Saloon\Exceptions\RequestException;
use Saloon\Http\Responses\SimulatedAbstractResponse;
use Saloon\Tests\Fixtures\Responses\UserData;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Tests\Fixtures\Requests\ErrorRequest;
use Saloon\Tests\Fixtures\Responses\UserResponse;
use Saloon\Tests\Fixtures\Requests\UserRequestWithCustomResponse;

test('an asynchronous request can be made successfully', function () {
    $request = new UserRequest();
    $promise = $request->sendAsync();

    expect($promise)->toBeInstanceOf(PromiseInterface::class);

    $response = $promise->wait();

    expect($response)->toBeInstanceOf(Response::class);

    $data = $response->json();

    expect($response->isMocked())->toBeFalse();
    expect($response->status())->toEqual(200);

    expect($data)->toEqual([
        'name' => 'Sammyjo20',
        'actual_name' => 'Sam',
        'twitter' => '@carre_sam',
    ]);
});

test('an asynchronous request can handle an exception properly', function () {
    $request = new ErrorRequest();
    $promise = $request->sendAsync();

    $this->expectException(RequestException::class);

    $promise->wait();
});

test('an asynchronous response will still be passed through response middleware', function () {
    $mockClient = new MockClient([
        MockResponse::make(200, ['name' => 'Sam']),
    ]);

    $request = new UserRequest();

    SimulatedAbstractResponse::macro('setValue', function ($value) {
        $this->value = $value;
    });

    SimulatedAbstractResponse::macro('getValue', function () {
        return $this->value;
    });

    $request->middleware()->onResponse(function (Response $response) {
        $response->setValue(true);
    });

    $promise = $request->sendAsync($mockClient);
    $response = $promise->wait();

    expect($response->getValue())->toBeTrue();
});

test('an asynchronous request will return a custom response', function () {
    $mockClient = new MockClient([
        MockResponse::make(200, ['foo' => 'bar']),
    ]);

    $request = new UserRequestWithCustomResponse();

    $promise = $request->sendAsync($mockClient);

    $response = $promise->wait();

    expect($response)->toBeInstanceOf(UserResponse::class);
    expect($response)->customCastMethod()->toBeInstanceOf(UserData::class);
    expect($response)->foo()->toBe('bar');
});

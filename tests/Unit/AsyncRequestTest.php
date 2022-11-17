<?php declare(strict_types=1);

use Saloon\Contracts\Response;
use GuzzleHttp\Promise\Promise;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Exceptions\RequestException;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Exception\ConnectException;
use Saloon\Tests\Fixtures\Requests\UserRequest;

test('an asynchronous request will return a saloon response on a successful request', function () {
    $mockClient = new MockClient([
        MockResponse::make(200, ['name' => 'Sam']),
    ]);

    $request = new UserRequest;
    $promise = $request->sendAsync($mockClient);

    expect($promise)->toBeInstanceOf(PromiseInterface::class);

    $response = $promise->wait();

    expect($response)->toBeInstanceOf(Response::class);
    expect($response->json())->toEqual(['name' => 'Sam']);
    expect($response->status())->toEqual(200);
});

test('an asynchronous request will throw a saloon exception on an unsuccessful request', function () {
    $mockClient = new MockClient([
        MockResponse::make(500, ['error' => 'Server Error']),
    ]);

    $request = new UserRequest;
    $promise = $request->sendAsync($mockClient);

    expect($promise)->toBeInstanceOf(Promise::class);

    // Todo: not working

    try {
        $promise->wait();
    } catch (Exception $exception) {
        expect($exception)->toBeInstanceOf(RequestException::class);

        $response = $exception->getResponse();

        expect($response)->toBeInstanceOf(Response::class);
        expect($response->json())->toEqual(['error' => 'Server Error']);
        expect($response->status())->toEqual(500);
        expect($response->getGuzzleException())->toBeInstanceOf(RequestException::class);
    }
});

test('an asynchronous request will return a connect exception if a connection error happens', function () {
    $mockClient = new MockClient([
        MockResponse::make(200, ['name' => 'Patrick'])->throw(fn ($guzzleRequest) => new ConnectException('Unable to connect!', $guzzleRequest)),
    ]);

    $request = new UserRequest;
    $promise = $request->sendAsync($mockClient);

    try {
        $promise->wait();
    } catch (Exception $exception) {
        expect($exception)->toBeInstanceOf(ConnectException::class);
        expect($exception->getMessage())->toEqual('Unable to connect!');
    }
});

test('if you chain an asynchronous request you can have a AbstractResponse', function () {
    $mockClient = new MockClient([
        MockResponse::make(200, ['name' => 'Sam']),
    ]);

    $request = new UserRequest;
    $promise = $request->sendAsync($mockClient);

    $promise->then(
        function (Response $response) {
            expect($response)->toBeInstanceOf(Response::class);
        }
    );

    $promise->wait();
});

test('if you chain an erroneous asynchronous request the error can be caught in the rejection handler', function () {
    $mockClient = new MockClient([
        MockResponse::make(500, ['error' => 'Server Error']),
    ]);

    $request = new UserRequest;
    $promise = $request->sendAsync($mockClient);

    $promise->then(
        null,
        function (RequestException $exception) {
            $response = $exception->getResponse();

            expect($response)->toBeInstanceOf(Response::class);
            expect($response->status())->toEqual(500);
            expect($response->getGuzzleException())->toBeInstanceOf(RequestException::class);
        }
    );

    try {
        $promise->wait();
    } catch (\Exception $ex) {
        //
    }
});

test('if a connection exception happens it will be provided in the rejection handler', function () {
    $mockClient = new MockClient([
        MockResponse::make(200, ['name' => 'Patrick'])->throw(fn ($guzzleRequest) => new ConnectException('Unable to connect!', $guzzleRequest)),
    ]);

    $request = new UserRequest;
    $promise = $request->sendAsync($mockClient);

    $promise->then(
        null,
        function (ConnectException $exception) {
            expect($exception->getMessage())->toEqual('Unable to connect!');
        }
    );

    try {
        $promise->wait();
    } catch (\Exception $ex) {
        //
    }
});

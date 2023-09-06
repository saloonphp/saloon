<?php

declare(strict_types=1);

use Saloon\Contracts\Response;
use Saloon\Http\Faking\MockClient;
use Saloon\Contracts\PendingRequest;
use Saloon\Http\Faking\MockResponse;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Tests\Fixtures\Requests\AlwaysThrowRequest;

test('a user can register a request and response debugger on the connector and request', function () {
    $mockClient = new MockClient([
        new MockResponse(['name' => 'Sam']),
    ]);

    $connector = new TestConnector;
    $connector->withMockClient($mockClient);

    $connectorRequestDebuggerValid = false;
    $connectorResponseDebuggerValid = false;

    $requestClassRequestDebuggerValid = false;
    $requestClassResponseDebuggerValid = false;

    // The connector can register a callback to debug the request

    $connector->debugRequest(function (PendingRequest $pendingRequest, RequestInterface $psrRequest) use (&$connectorRequestDebuggerValid) {
        expect($pendingRequest)->toBeInstanceOf(PendingRequest::class);
        expect($psrRequest)->toBeInstanceOf(RequestInterface::class);

        $connectorRequestDebuggerValid = true;
    });

    // The connector can register a callback to debug the response

    $connector->debugResponse(function (Response $response, ResponseInterface $psrResponse) use (&$connectorResponseDebuggerValid) {
        expect($response)->toBeInstanceOf(Response::class);
        expect($psrResponse)->toBeInstanceOf(ResponseInterface::class);

        $connectorResponseDebuggerValid = true;
    });

    $request = new UserRequest;

    // The request can register a callback to debug the request

    $request->debugRequest(function (PendingRequest $pendingRequest, RequestInterface $psrRequest) use (&$requestClassRequestDebuggerValid) {
        expect($pendingRequest)->toBeInstanceOf(PendingRequest::class);
        expect($psrRequest)->toBeInstanceOf(RequestInterface::class);

        $requestClassRequestDebuggerValid = true;
    });

    // The request can register a callback to debug the response

    $request->debugResponse(function (Response $response, ResponseInterface $psrResponse) use (&$requestClassResponseDebuggerValid) {
        expect($response)->toBeInstanceOf(Response::class);
        expect($psrResponse)->toBeInstanceOf(ResponseInterface::class);

        $requestClassResponseDebuggerValid = true;
    });

    $connector->send($request);

    // Check these are all true

    expect($connectorRequestDebuggerValid)->toBeTrue();
    expect($connectorResponseDebuggerValid)->toBeTrue();
    expect($requestClassRequestDebuggerValid)->toBeTrue();
    expect($requestClassResponseDebuggerValid)->toBeTrue();
});

test('the response debugger is always executed before user middleware', function () {
    $mockClient = new MockClient([
        new MockResponse(['name' => 'Sam']),
    ]);

    $connector = new TestConnector;
    $connector->withMockClient($mockClient);
    $request = new UserRequest;

    $middlewareOrder = [];

    $connector->middleware()->onResponse(function () use (&$middlewareOrder) {
        $middlewareOrder[] = 'A';
    });

    $request->middleware()->onResponse(function () use (&$middlewareOrder) {
        $middlewareOrder[] = 'B';
    });

    $connector->debugResponse(function () use (&$middlewareOrder) {
        $middlewareOrder[] = 'C';
    });

    $request->debugResponse(function () use (&$middlewareOrder) {
        $middlewareOrder[] = 'D';
    });

    $connector->send($request);

    // Even though the user has registered response middleware, the debugger should always come first.

    expect($middlewareOrder)->toBe(['C', 'D', 'A', 'B']);
});

test('the response debugger is always executed before the AlwaysThrowOnErrors trait', function () {
    $mockClient = new MockClient([
        new MockResponse(['name' => 'Sam'], 500),
    ]);

    $connector = new TestConnector;
    $connector->withMockClient($mockClient);
    $request = new AlwaysThrowRequest;

    $middlewareCount = 0;

    $connector->debugResponse(function () use (&$middlewareCount) {
        $middlewareCount++;
    });

    $request->debugResponse(function () use (&$middlewareCount) {
        $middlewareCount++;
    });

    try {
        $connector->send($request);
    } catch (Exception $exception) {
        expect($middlewareCount)->toBe(2);
    }
});

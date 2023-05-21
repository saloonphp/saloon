<?php

declare(strict_types=1);

use Saloon\Enums\Method;
use Saloon\Debugging\Debugger;
use Saloon\Http\Faking\MockClient;
use Saloon\Repositories\ArrayStore;
use Saloon\Http\Faking\MockResponse;
use Saloon\Exceptions\DebuggingDriverException;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Tests\Fixtures\Debuggers\ArrayDebugger;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Tests\Fixtures\Requests\HasXmlBodyRequest;
use Saloon\Tests\Fixtures\Requests\HasFormBodyRequest;
use Saloon\Tests\Fixtures\Requests\HasJsonBodyRequest;
use Saloon\Tests\Fixtures\Requests\HasStringBodyRequest;
use Saloon\Tests\Fixtures\Requests\HasMultipartBodyRequest;
use Saloon\Tests\Fixtures\Debuggers\MissingDependencyDebugger;

test('it will debug a request being sent', function () {
    $connector = new TestConnector;

    $arrayDebugger = new ArrayDebugger;

    $connector->debug(function (Debugger $debugger) use ($arrayDebugger) {
        $debugger->showRequestAndResponse()->usingDriver($arrayDebugger);
    });

    $request = new UserRequest;
    $request->query()->add('example', 'query');
    $request->config()->add('timeout', 30);

    $response = $connector->send($request);

    expect($response->status())->toEqual(200);

    $debuggedRequests = $arrayDebugger->getRequests();
    $debuggedResponses = $arrayDebugger->getResponses();

    expect($debuggedRequests)->toHaveCount(1);
    expect($debuggedResponses)->toHaveCount(1);

    expect($debuggedRequests[0])->toEqual([
        'method' => Method::GET,
        'uri' => 'https://tests.saloon.dev/api/user',
        'request_headers' => new ArrayStore(['Accept' => 'application/json']),
        'request_query' => new ArrayStore(['example' => 'query']),
        'request_payload' => null,
        'sender_config' => new ArrayStore(['timeout' => 30]),
        'request_class' => $request::class,
        'connector_class' => $connector::class,
        'sender_class' => $connector->sender()::class,
    ]);

    expect($debuggedResponses[0])->toEqual([
        'response_status' => $response->status(),
        'response_headers' => $response->headers(),
        'response_body' => $response->json(),
        'response_class' => $response::class,
    ]);
});

test('if the debugger has been configured to run before sent it will log the request', function () {
    $mockClient = new MockClient([
        new MockResponse(['name' => 'Sam'], 201, ['X-Yee' => 'Haw', 'Content-Type' => 'application/json']),
    ]);

    $connector = new TestConnector;
    $connector->withMockClient($mockClient);

    $arrayDebugger = new ArrayDebugger;

    $connector->debug(function (Debugger $debugger) use ($arrayDebugger) {
        $debugger->showRequest()->usingDriver($arrayDebugger);
    });

    $connector->send(new UserRequest);

    $debuggedRequests = $arrayDebugger->getRequests();
    $debuggedResponses = $arrayDebugger->getResponses();

    expect($debuggedRequests)->toHaveCount(1);
    expect($debuggedResponses)->toHaveCount(0);
});

test('if the debugger has been configured to run after sent it will log the response', function () {
    $mockClient = new MockClient([
        new MockResponse(['name' => 'Sam'], 201, ['X-Yee' => 'Haw', 'Content-Type' => 'application/json']),
    ]);

    $connector = new TestConnector;
    $connector->withMockClient($mockClient);

    $arrayDebugger = new ArrayDebugger;

    $connector->debug(function (Debugger $debugger) use ($arrayDebugger) {
        $debugger->showResponse()->usingDriver($arrayDebugger);
    });

    $connector->send(new UserRequest);

    $debuggedRequests = $arrayDebugger->getRequests();
    $debuggedResponses = $arrayDebugger->getResponses();

    expect($debuggedRequests)->toHaveCount(0);
    expect($debuggedResponses)->toHaveCount(1);
});

test('it will output the request payload if there is a body sent', function ($request) {
    $mockClient = new MockClient([
        new MockResponse(['name' => 'Sam'], 201, ['X-Yee' => 'Haw', 'Content-Type' => ['application/json','charset=utf-8']]),
    ]);

    $connector = new TestConnector;
    $connector->withMockClient($mockClient);

    $arrayDebugger = new ArrayDebugger;

    $connector->debug(function (Debugger $debugger) use ($arrayDebugger) {
        $debugger->showRequest()->usingDriver($arrayDebugger);
    });

    $connector->send($request);

    $debuggedRequests = $arrayDebugger->getRequests();

    expect($debuggedRequests)->toHaveCount(1);

    expect($debuggedRequests[0]['request_payload'])->toEqual($request->body());
})->with([
    new HasJsonBodyRequest,
    new HasFormBodyRequest,
    new HasMultipartBodyRequest,
    new HasXmlBodyRequest,
    new HasStringBodyRequest,
]);

test('if a response does not provide a content type of "application/json" then the body is not formatted', function () {
    $mockClient = new MockClient([
        new MockResponse('Hello World', 201, ['X-Yee' => 'Haw']),
    ]);

    $connector = new TestConnector;
    $connector->withMockClient($mockClient);

    $arrayDebugger = new ArrayDebugger;

    $connector->debug(function (Debugger $debugger) use ($arrayDebugger) {
        $debugger->showResponse()->usingDriver($arrayDebugger);
    });

    $connector->send(new UserRequest);

    $debuggedResponses = $arrayDebugger->getResponses();

    expect($debuggedResponses)->toHaveCount(1);

    expect($debuggedResponses[0]['response_body'])->toEqual('Hello World');
});

test('it throws an exception if the debugging driver does not have the dependencies installed', function () {
    $mockClient = new MockClient([
        new MockResponse('Hello World', 201, ['X-Yee' => 'Haw']),
    ]);

    $connector = new TestConnector;
    $connector->withMockClient($mockClient);

    $this->expectException(DebuggingDriverException::class);
    $this->expectExceptionMessage('The driver "missingDependency" cannot be used because its dependencies are not installed.');

    $connector->debug(function (Debugger $debugger) {
        $debugger->registerDriver(new MissingDependencyDebugger);

        $debugger->showResponse()->usingDriver('missingDependency');
    });
});

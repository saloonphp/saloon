<?php declare(strict_types=1);

use Psr\Http\Message\RequestInterface;
use Saloon\Http\Faking\MockClient;
use Saloon\Managers\RequestManager;
use Saloon\Http\Faking\MockResponse;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Tests\Fixtures\Requests\TimeoutRequest;
use Saloon\Tests\Fixtures\Connectors\TimeoutConnector;

test('a request is given a default timeout and connect timeout', function () {
    $mockClient = new MockClient([new MockResponse()]);

    $request = UserRequest::make();

    $request->addHandler('test', function (callable $handler) {
        return function (RequestInterface $request, array $options) use ($handler) {
            expect($options['connect_timeout'])->toEqual(10.0);
            expect($options['timeout'])->toEqual(30.0);

            return $handler($request, $options);
        };
    });

    $request->send($mockClient);
});

test('a request can set a timeout and connect timeout', function () {
    $requestManager = new RequestManager(new TimeoutRequest);
    $requestManager->hydrate();

    $config = $requestManager->getConfig();

    expect($config)->toHaveKey('connect_timeout', 1);
    expect($config)->toHaveKey('timeout', 2);
});

test('a connector is given a default timeout and connect timeout', function () {
    $mockClient = new MockClient([new MockResponse()]);

    $request = (new UserRequest)->setConnector(new TimeoutConnector);

    $request->addHandler('test', function (callable $handler) {
        return function (RequestInterface $request, array $options) use ($handler) {
            expect($options['connect_timeout'])->toEqual(10.0);
            expect($options['timeout'])->toEqual(5.0);

            return $handler($request, $options);
        };
    });

    $requestManager = $request->getRequestManager();
    $requestManager->hydrate();

    $config = $requestManager->getConfig();

    expect($config)->toHaveKey('connect_timeout', 10);
    expect($config)->toHaveKey('timeout', 5);

    $request->send($mockClient);
});

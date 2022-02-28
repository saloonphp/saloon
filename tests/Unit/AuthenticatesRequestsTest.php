<?php

use Psr\Http\Message\RequestInterface;
use Sammyjo20\Saloon\Http\MockResponse;
use Sammyjo20\Saloon\Clients\MockClient;
use Sammyjo20\Saloon\Tests\Resources\Requests\UserRequest;

test('you can add basic auth to a request', function () {
    $mockClient = new MockClient([
        MockResponse::make(),
    ]);

    $request = new UserRequest;
    $request->withBasicAuth('Sammyjo20', 'Cowboy1');

    $request->addHandler('test', function (callable $handler) {
        return function (RequestInterface $request, array $options) use ($handler) {
            expect($options['auth'])->toBeArray();
            expect($options['auth'][0])->toEqual('Sammyjo20');
            expect($options['auth'][1])->toEqual('Cowboy1');

            return $handler($request, $options);
        };
    });

    $requestManager = $request->getRequestManager();
    $requestManager->hydrate();

    $config = $requestManager->getConfig();

    expect($config['auth'])->toBeArray();
    expect($config['auth'][0])->toEqual('Sammyjo20');
    expect($config['auth'][1])->toEqual('Cowboy1');

    $request->send($mockClient);
});

test('you can add digest auth to a request', function () {
    $mockClient = new MockClient([
        MockResponse::make(),
    ]);

    $request = new UserRequest;
    $request->withDigestAuth('Sammyjo20', 'Cowboy1');

    $request->addHandler('test', function (callable $handler) {
        return function (RequestInterface $request, array $options) use ($handler) {
            expect($options['auth'])->toBeArray();
            expect($options['auth'][0])->toEqual('Sammyjo20');
            expect($options['auth'][1])->toEqual('Cowboy1');
            expect($options['auth'][2])->toEqual('digest');

            return $handler($request, $options);
        };
    });

    $requestManager = $request->getRequestManager();
    $requestManager->hydrate();

    $config = $requestManager->getConfig();

    expect($config['auth'])->toBeArray();
    expect($config['auth'][0])->toEqual('Sammyjo20');
    expect($config['auth'][1])->toEqual('Cowboy1');
    expect($config['auth'][2])->toEqual('digest');

    $request->send($mockClient);
});

test('you can attach an authorization token to a request', function () {
    $mockClient = new MockClient([
        MockResponse::make(),
    ]);

    $request = UserRequest::make()->withToken('Sammyjo20');

    $request->addHandler('test', function (callable $handler) {
        return function (RequestInterface $request, array $options) use ($handler) {
            expect($request->getHeaders()['Authorization'])->toEqual(['Bearer Sammyjo20']);

            return $handler($request, $options);
        };
    });

    $requestManager = $request->getRequestManager();
    $requestManager->hydrate();

    $headers = $requestManager->getHeaders();

    expect($headers)->toHaveKey('Authorization', 'Bearer Sammyjo20');

    $request->send($mockClient);
});

<?php

declare(strict_types=1);

use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Uri;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Response;
use Saloon\Http\Senders\GuzzleSender;
use Psr\Http\Message\RequestInterface;
use GuzzleHttp\Promise\FulfilledPromise;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Tests\Fixtures\Connectors\TestConnector;

test('the guzzle sender will send to the right url using the correct method', function () {
    $request = new UserRequest;
    $sender = $request->connector()->sender();

    $pendingRequest = $request->createPendingRequest();

    $sender->addMiddleware(function (callable $handler) use ($pendingRequest) {
        return function (RequestInterface $request, array $options) use ($handler, $pendingRequest) {
            expect($request->getMethod())->toEqual($pendingRequest->getMethod()->value);

            $uri = $request->getUri();
            $saloonUri = new Uri($pendingRequest->getUrl());

            expect($uri)->toEqual($saloonUri);

            // Return fulfilled promise to fake response

            return new FulfilledPromise(new Response());
        };
    });

    $request->send();
});

test('the guzzle sender will send all headers, query parameters and config', function () {
    $request = new UserRequest;

    $request->config()->add('timeout', 120);
    $request->config()->add('debug', true);
    $request->query()->add('shanty', 'yes');
    $request->query()->add('sing', 'yes');
    $request->headers()->add('X-Bound-For', 'South-Australia');
    $request->headers()->add('X-Fancy', ['keyOne' => 'valOne', 'keyTwo' => 'valTwo']);

    $sender = $request->connector()->sender();

    $sender->addMiddleware(function (callable $handler) {
        return function (RequestInterface $request, array $options) use ($handler) {
            expect($options['timeout'])->toEqual(120);
            expect($options['debug'])->toBeTrue();
            expect($request->getUri()->getQuery())->toEqual('shanty=yes&sing=yes');
            expect($request->getHeaderLine('X-Bound-For'))->toEqual('South-Australia');
            expect($request->getHeaderLine('X-Fancy'))->toEqual('valOne, valTwo');

            // Return fulfilled promise to fake response

            return new FulfilledPromise(new Response());
        };
    });

    $request->send();
});

test('the guzzle sender has the default handler stack configured by default', function () {
    $connector = new TestConnector;
    $sender = $connector->sender();

    expect($sender)->toBeInstanceOf(GuzzleSender::class);

    $handlerStack = $sender->getHandlerStack();

    // The HandlerStack::create() loads important default middleware

    expect($handlerStack)->toEqual(HandlerStack::create());
});

test('the guzzle sender has default options configured', function () {
    $connector = new TestConnector;
    $sender = $connector->sender();

    expect($sender)->toBeInstanceOf(GuzzleSender::class);

    $client = $sender->getGuzzleClient();

    $freshClient = new Client([
        'connect_timeout' => 10,
        'timeout' => 30,
        'http_errors' => true,
    ]);

    expect($freshClient->getConfig())->toEqual($client->getConfig());
});

test('the guzzle sender will send the correct body for the HasBody trait', function () {
})->skip('TODO');

test('the guzzle sender will send the correct body for the HasXmlBody trait', function () {
})->skip('TODO');

test('the guzzle sender will send the correct body for the HasJsonBody trait', function () {
})->skip('TODO');

test('the guzzle sender will send the correct body for the HasMultipartBody trait', function () {
})->skip('TODO');

test('the guzzle sender will send the correct body for the HasFormBody trait', function () {
})->skip('TODO');

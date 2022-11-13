<?php declare(strict_types=1);

use GuzzleHttp\Psr7\Uri;
use Psr\Http\Message\RequestInterface;
use Saloon\Tests\Fixtures\Requests\UserRequest;

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

            return $handler($request, $options);
        };
    });

    $request->send();
});

test('the guzzle sender will send all headers, query parameters and config', function () {
    $request = new UserRequest;

    $request->config()->add('timeout', 120);
    $request->config()->add('debug', true);
    $request->queryParameters()->add('shanty', 'yes');
    $request->headers()->add('X-Bound-For', 'South-Australia');
    $request->headers()->add('X-Fancy', ['keyOne' => 'valOne', 'keyTwo' => 'valTwo']);

    $sender = $request->connector()->sender();

    $sender->addMiddleware(function (callable $handler) {
        return function (RequestInterface $request, array $options) use ($handler) {
            expect($options['timeout'])->toEqual(120);
            expect($options['debug'])->toBeTrue();
            expect($request->getUri()->getQuery())->toEqual('shanty=yes');
            expect($request->getHeaderLine('X-Bound-For'))->toEqual('South-Australia');
            expect($request->getHeaderLine('X-Fancy'))->toEqual('valOne, valTwo');

            return $handler($request, $options);
        };
    });

    $request->send();
});

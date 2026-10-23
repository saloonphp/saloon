<?php

declare(strict_types=1);

use Saloon\Enums\Method;
use Saloon\Http\Request;
use Saloon\Http\Connector;
use GuzzleHttp\Handler\MockHandler;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\NetworkException;
use GuzzleHttp\Psr7\Request as PsrRequest;
use GuzzleHttp\Psr7\Response as PsrResponse;
use GuzzleHttp\Exception\NetworkTimeoutException;
use GuzzleHttp\Exception\ResponseTimeoutException;
use Saloon\Exceptions\Request\FatalRequestException;

beforeEach(function () {
    if (! class_exists(NetworkException::class)) {
        $this->markTestSkipped('Requires Guzzle 8 (NetworkException).');
    }
});

test('guzzle 8 transfer failures are converted to fatal request exceptions', function (callable $makeException) {
    $psrRequest = new PsrRequest('GET', 'https://example.test/resource');

    $connector = new class extends Connector {
        public function resolveBaseUrl(): string
        {
            return 'https://example.test';
        }
    };

    $request = new class extends Request {
        protected Method $method = Method::GET;

        public function resolveEndpoint(): string
        {
            return '/resource';
        }
    };

    $mock = new MockHandler([$makeException($psrRequest)]);
    $connector->sender()->getHandlerStack()->setHandler($mock);

    $connector->send($request);
})->throws(FatalRequestException::class)->with([
    'ConnectException' => fn (PsrRequest $psrRequest) => new ConnectException('connect failed', $psrRequest),
    'NetworkTimeoutException' => fn (PsrRequest $psrRequest) => new NetworkTimeoutException('timed out before headers', $psrRequest),
    'NetworkException' => fn (PsrRequest $psrRequest) => new NetworkException('empty reply', $psrRequest),
    'ResponseTimeoutException' => fn (PsrRequest $psrRequest) => new ResponseTimeoutException(
        'timed out after headers',
        $psrRequest,
        new PsrResponse(200, [], '{"partial":'),
    ),
]);

test('guzzle 8 transfer failures are converted to fatal request exceptions when using sendAsync', function () {
    $psrRequest = new PsrRequest('GET', 'https://example.test/resource');

    $connector = new class extends Connector {
        public function resolveBaseUrl(): string
        {
            return 'https://example.test';
        }
    };

    $request = new class extends Request {
        protected Method $method = Method::GET;

        public function resolveEndpoint(): string
        {
            return '/resource';
        }
    };

    $mock = new MockHandler([
        new ResponseTimeoutException(
            'timed out after headers',
            $psrRequest,
            new PsrResponse(200, [], '{"partial":'),
        ),
    ]);
    $connector->sender()->getHandlerStack()->setHandler($mock);

    $connector->sendAsync($request)->wait();
})->throws(FatalRequestException::class);

test('guzzle 8 network failures participate in connector retries when using send', function () {
    $psrRequest = new PsrRequest('GET', 'https://example.test/resource');
    $makeException = fn () => new NetworkException('empty reply', $psrRequest);

    $connector = new class extends Connector {
        public ?int $tries = 3;

        public function resolveBaseUrl(): string
        {
            return 'https://example.test';
        }
    };

    $request = new class extends Request {
        protected Method $method = Method::GET;

        public function resolveEndpoint(): string
        {
            return '/resource';
        }
    };

    $mock = new MockHandler([$makeException(), $makeException(), $makeException()]);
    $connector->sender()->getHandlerStack()->setHandler($mock);

    try {
        $connector->send($request);
    } catch (FatalRequestException) {
        //
    }

    expect($mock)->toHaveCount(0);
});

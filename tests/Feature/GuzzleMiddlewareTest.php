<?php declare(strict_types=1);

use GuzzleHttp\Middleware;
use Saloon\Http\Senders\GuzzleSender;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Saloon\Tests\Fixtures\Requests\UserRequest;

test('you can add middleware to the guzzle sender', function () {
    $request = new UserRequest();

    // Use Saloon's middleware pipeline...

    /** @var GuzzleSender $sender */
    $sender = $request->sender();

    $sender->pushMiddleware(Middleware::mapRequest(function (RequestInterface $r) {
        return $r->withHeader('X-Foo', 'Bar');
    }), 'a');

    $sender->pushMiddlewareAfter('a', Middleware::mapRequest(function (RequestInterface $r) {
        return $r->withHeader('X-Foo', 'Baz');
    }));

    $sender->pushMiddleware(Middleware::mapResponse(function (ResponseInterface $response) {
        return $response->withHeader('X-Foo', 'bar');
    }), 'b');

    $sender->removeMiddleware('b');

    $response = $request->send();

    dd($response->json());
})->skip('SAM TODO');

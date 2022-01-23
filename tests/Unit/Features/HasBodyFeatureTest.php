<?php

use Psr\Http\Message\RequestInterface;
use Sammyjo20\Saloon\Tests\Resources\Requests\HasBodyRequest;

test('with the hasBody trait, you can pass in a string body response', function () {
    $request = new HasBodyRequest;

    $request->addHandler('hasBodyHandler', function (callable $handler) {
        return function (RequestInterface $request, array $options) use ($handler) {
            expect($request->getBody()->getContents())->toEqual('xml');

            return $handler($request, $options);
        };
    });

    $request->send();
});

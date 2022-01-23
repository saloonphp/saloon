<?php

use Psr\Http\Message\RequestInterface;
use Sammyjo20\Saloon\Exceptions\SaloonHasBodyException;
use Sammyjo20\Saloon\Tests\Resources\Requests\HasBodyConnectorRequest;
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

test('it throws an exception if you try to add the hasBody trait to both the connector and the request', function () {
    $request = new HasBodyConnectorRequest;

    $this->expectException(SaloonHasBodyException::class);

    $request->send();
});

<?php

use Saloon\Enums\Method;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\PendingRequest;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Tests\Fixtures\Requests\UserRequest;

test('you can overwrite the url and the method of the pending request', function () {
    $connector = new TestConnector;

    $connector->withMockClient(new MockClient([
        new MockResponse(['name' => 'Sam'])
    ]));

    $connector->middleware()->onRequest(function (PendingRequest $pendingRequest) {
        $pendingRequest->setUrl('https://other-endpoint.co.uk' . $pendingRequest->getRequest()->resolveEndpoint());
        $pendingRequest->setMethod(Method::POST);
    });

    $request = new UserRequest;

    expect($request->getMethod())->toEqual(Method::GET);

    $response = $connector->send(new UserRequest);
    $pendingRequest = $response->getPendingRequest();

    expect($pendingRequest->getUrl())->toEqual('https://other-endpoint.co.uk/user');
    expect($pendingRequest->getMethod())->toEqual(Method::POST);
});

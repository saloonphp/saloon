<?php

use Sammyjo20\Saloon\Clients\MockClient;
use Sammyjo20\Saloon\Constants\MockStrategies;
use Sammyjo20\Saloon\Exceptions\SaloonNoMockResponsesProvidedException;
use Sammyjo20\Saloon\Http\MockResponse;
use Sammyjo20\Saloon\Tests\Resources\Requests\UserRequest;

test('if you dont pas sin a mock client to the saloon request it will not be in mocking mode', function () {
    $request = new UserRequest();
    $requestManager = $request->getRequestManager();

    expect($requestManager->isMocking())->toBeFalse();
    expect($requestManager->getMockStrategy())->toBeNull();
});

test('you can pass a mock client to the saloon request and it will be in mock mode', function () {
    $request = new UserRequest();
    $mockClient = new MockClient([new MockResponse([], 200)]);

    $requestManager = $request->getRequestManager($mockClient);

    expect($requestManager->isMocking())->toBeTrue();
    expect($requestManager->getMockStrategy())->toEqual(MockStrategies::SALOON);
});

test('you cant pass a mock client without any responses', function () {
    $mockClient = new MockClient();
    $request = new UserRequest();

    $this->expectException(SaloonNoMockResponsesProvidedException::class);

    $request->send($mockClient);
});

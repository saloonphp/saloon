<?php declare(strict_types=1);

use Sammyjo20\Saloon\Tests\Fixtures\Requests\UserRequest;
use Sammyjo20\Saloon\Tests\Fixtures\Requests\ErrorRequest;
use Sammyjo20\Saloon\Tests\Fixtures\Connectors\RequestSelectionConnector;

test('you can view the available requests', function () {
    $connector = new RequestSelectionConnector;
    $requests = $connector->getRegisteredRequests();

    expect($requests)->toBeArray();
    expect($requests)->toHaveKey('getMyUser');
    expect($requests)->toHaveKey('errorRequest');
    expect($requests['getMyUser'])->toEqual(UserRequest::class);
    expect($requests['errorRequest'])->toEqual(ErrorRequest::class);
});

test('using the forwardCallToRequest method will instantiate a request with the same connector', function () {
    $connector = new RequestSelectionConnector('howdy-key');
    $request = $connector->getUser(1, 2); // Uses forwardCallToRequest

    expect($request)->toBeInstanceOf(UserRequest::class);
    expect($request)->getConnector()->toBe($connector);
    expect($request)->getConnector()->apiKey->toEqual('howdy-key');
});

test('you can check if a request exists', function () {
    $connector = new RequestSelectionConnector;

    expect($connector)->requestExists('getUser')->toBeTrue(); // Method is actually a method
    expect($connector)->requestExists('getMyUser')->toBeTrue(); // Method is manually registered
    expect($connector)->requestExists('errorRequest')->toBeTrue(); // Method key is auto generated
    expect($connector)->requestExists('missingRequest')->toBeFalse();
});

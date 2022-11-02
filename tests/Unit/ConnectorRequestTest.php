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

<?php

use Sammyjo20\Saloon\Tests\Resources\Connectors\TestConnector;
use Sammyjo20\Saloon\Tests\Resources\Connectors\RequestSelectionConnector;

test('a connector class can be instantiated using the make method', function () {
    $connectorA = TestConnector::make();

    expect($connectorA)->toBeInstanceOf(TestConnector::class);

    $connectorB = RequestSelectionConnector::make('yee-haw-1-2-3');

    expect($connectorB)->toBeInstanceOf(RequestSelectionConnector::class);
    expect($connectorB)->apiKey->toEqual('yee-haw-1-2-3');
});

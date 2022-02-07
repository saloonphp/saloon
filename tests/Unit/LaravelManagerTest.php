<?php

use Sammyjo20\Saloon\Clients\MockClient;
use Sammyjo20\Saloon\Managers\LaravelManager;

test('the manager can have mocking mode turned on', function () {
    $laravelManager = new LaravelManager();

    $laravelManager->setIsMocking(true);

    expect($laravelManager)->isMocking()->toBeTrue();

    $laravelManager->setIsMocking(false);

    expect($laravelManager)->isMocking()->toBeFalse();
});

test('the manager can have a mock client assigned to it ', function () {
    $laravelManager = new LaravelManager();
    $mockClient = new MockClient();

    $laravelManager->setMockClient($mockClient);

    expect($laravelManager)->getMockClient()->toBe($mockClient);
});

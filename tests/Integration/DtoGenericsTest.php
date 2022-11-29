<?php

declare(strict_types=1);

use Saloon\Tests\Integration\SteamConnector;

it('can resolve the dto return type based on generics', function (): void {
    $connector = new SteamConnector();
    $response = $connector->getAppList();
    $response->getRequest()->foobar;
    $apps = $response->dto();
})->only();

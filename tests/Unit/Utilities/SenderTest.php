<?php

declare(strict_types=1);

use Saloon\Http\Senders\GuzzleSender;
use Saloon\Tests\Fixtures\Senders\ArraySender;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Tests\Fixtures\Connectors\ArraySenderConnector;
use Saloon\Tests\Fixtures\Connectors\ArraySenderDefaultMethodConnector;

test('the default sender on all connectors is the guzzle sender', function () {
    $connector = new TestConnector();
    $sender = $connector->sender();

    expect($sender)->toBeInstanceOf(GuzzleSender::class);

    // Test the same instance is re-used

    expect($connector->sender())->toBe($sender);
});

test('you can overwrite the sender on a connector using the property', function () {
    $connector = new ArraySenderConnector();
    $sender = $connector->sender();

    expect($sender)->toBeInstanceOf(ArraySender::class);
    expect($connector->sender())->toBe($sender);

    // Test using the connector with the custom sender

    $request = new UserRequest();
    $response = $connector->send($request);

    expect($response->headers()->all())->toEqual(['X-Fake' => true]);
    expect($response->body())->toEqual('Default');
});

test('you can overwrite the sender on a connector using the defaultSender method', function () {
    $connector = new ArraySenderDefaultMethodConnector();
    $sender = $connector->sender();

    expect($sender)->toBeInstanceOf(ArraySender::class);
    expect($connector->sender())->toBe($sender);

    // Test using the connector with the custom sender

    $request = new UserRequest();
    $response = $connector->send($request);

    expect($response->headers()->all())->toEqual(['X-Fake' => true]);
    expect($response->body())->toEqual('Default');
});

test('it will throw an exception if the sender does not implement the sender interface', function () {
    $connector = new ArraySenderConnector();
    $connector->setDefaultSender(UserRequest::class);

    $this->expectException(TypeError::class);
    $this->expectExceptionMessage('Return value must be of type Saloon\Contracts\Sender, Saloon\Tests\Fixtures\Requests\UserRequest returned');

    $connector->sender();
});

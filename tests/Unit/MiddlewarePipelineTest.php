<?php

declare(strict_types=1);

use Saloon\Http\PendingRequest;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Responses\Response;
use Saloon\Http\Faking\MockResponse;
use Saloon\Helpers\MiddlewarePipeline;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Tests\Fixtures\Requests\ErrorRequest;

test('you can add a request pipe to the middleware', function () {
    $pipeline = new MiddlewarePipeline;

    $pipeline
        ->onRequest(function (PendingRequest $request) {
            $request->headers()->add('X-Pipe-One', 'Yee-Haw');
        })
        ->onRequest(function (PendingRequest $request) {
            $request->headers()->add('X-Pipe-Two', 'Howdy');
        });

    $pendingRequest = connector()->createPendingRequest(new UserRequest);
    $pendingRequest = $pipeline->executeRequestPipeline($pendingRequest);

    expect($pendingRequest->headers()->get('X-Pipe-One'))->toEqual('Yee-Haw');
    expect($pendingRequest->headers()->get('X-Pipe-Two'))->toEqual('Howdy');
});

test('if a request pipe returns a pending request, we will use that in the next step', function () {
    $pipeline = new MiddlewarePipeline;

    $errorRequest = connector()->createPendingRequest(new ErrorRequest);

    $pipeline
        ->onRequest(function (PendingRequest $request) use ($errorRequest) {
            $request->headers()->add('X-Pipe-One', 'Yee-Haw');

            return $errorRequest;
        });

    $pendingRequest = connector()->createPendingRequest(new UserRequest);
    $pendingRequest = $pipeline->executeRequestPipeline($pendingRequest);

    expect($pendingRequest)->toBe($errorRequest);
});

test('you can add a response pipe to the middleware', function () {
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Sam']),
    ]);

    $pipeline = new MiddlewarePipeline;

    $count = 0;

    $pipeline
        ->onResponse(function (Response $response) use (&$count) {
            expect($response)->toBeInstanceOf(Response::class);

            $count++;
        })
        ->onResponse(function (Response $response) use (&$count) {
            expect($response)->toBeInstanceOf(Response::class);

            $count++;
        });

    $response = connector()->send(new UserRequest, $mockClient);
    $response = $pipeline->executeResponsePipeline($response);

    expect($response)->toBeInstanceOf(Response::class);
    expect($count)->toBe(2);
});

test('if a response pipe returns a response, we will use that in the next step', function () {
    $mockClient = new MockClient([
        ErrorRequest::class => MockResponse::make(['error' => 'Server Error'], 500),
        UserRequest::class => MockResponse::make(['name' => 'Sam']),
    ]);

    $pipeline = new MiddlewarePipeline;

    $errorResponse = connector()->send(new ErrorRequest, $mockClient);

    $pipeline
        ->onResponse(function (Response $response) use ($errorResponse) {
            return $errorResponse;
        });

    $response = connector()->send(new UserRequest, $mockClient);
    $response = $pipeline->executeResponsePipeline($response);

    expect($response)->toBe($errorResponse);
});

test('you can merge a middleware pipeline together', closure: function () {
    $pipelineA = new MiddlewarePipeline;
    $pipelineB = new MiddlewarePipeline;

    $pipelineA
        ->onRequest(function (PendingRequest $request) {
            $request->headers()->add('X-Pipe-One', 'Yee-Haw');
        })
        ->onRequest(function (PendingRequest $request) {
            $request->headers()->add('X-Pipe-One', 'Howdy');
        })
        ->onResponse(function (Response $response) {
            return $response->throw();
        });

    expect($pipelineB->getRequestPipeline()->getPipes())->toBeEmpty();
    expect($pipelineB->getResponsePipeline()->getPipes())->toBeEmpty();

    $pipelineB->merge($pipelineA);

    expect($pipelineB->getRequestPipeline()->getPipes())->toHaveCount(2);
    expect($pipelineB->getResponsePipeline()->getPipes())->toHaveCount(1);
});

test('a request pipeline is run in order of pipes', function () {
    $pipeline = new MiddlewarePipeline;
    $names = [];

    $pipeline
        ->onRequest(function (PendingRequest $request) use (&$names) {
            $names[] = 'Sam';
        })
        ->onRequest(function (PendingRequest $request) use (&$names) {
            $names[] = 'Taylor';
        });

    $pendingRequest = connector()->createPendingRequest(new UserRequest);

    $pipeline->executeRequestPipeline($pendingRequest);

    expect($names)->toEqual(['Sam', 'Taylor']);
});

test('a request pipe can be added to the top of the pipeline', function () {
    $pipeline = new MiddlewarePipeline;
    $names = [];

    $pipeline
        ->onRequest(function (PendingRequest $request) use (&$names) {
            $names[] = 'Sam';
        })
        ->onRequest(function (PendingRequest $request) use (&$names) {
            $names[] = 'Taylor';
        }, true);

    $pendingRequest = connector()->createPendingRequest(new UserRequest);

    $pipeline->executeRequestPipeline($pendingRequest);

    expect($names)->toEqual(['Taylor', 'Sam']);
});

test('a response pipe is run in order of the pipes', function () {
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Sam']),
    ]);

    $names = [];

    $pipeline = new MiddlewarePipeline;

    $pipeline
        ->onResponse(function (Response $response) use (&$names) {
            $names[] = 'Sam';
        })
        ->onResponse(function (Response $response) use (&$names) {
            $names[] = 'Taylor';
        });

    $response = connector()->send(new UserRequest, $mockClient);

    $pipeline->executeResponsePipeline($response);

    expect($names)->toEqual(['Sam', 'Taylor']);
});

test('a response pipe can be added to the top of the pipeline', function () {
    $mockClient = new MockClient([
        MockResponse::make(['name' => 'Sam']),
    ]);

    $names = [];

    $pipeline = new MiddlewarePipeline;

    $pipeline
        ->onResponse(function (Response $response) use (&$names) {
            $names[] = 'Sam';
        })
        ->onResponse(function (Response $response) use (&$names) {
            $names[] = 'Taylor';
        }, true);

    $response = connector()->send(new UserRequest, $mockClient);

    $pipeline->executeResponsePipeline($response);

    expect($names)->toEqual(['Taylor', 'Sam']);
});

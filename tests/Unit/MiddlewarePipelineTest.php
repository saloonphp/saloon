<?php

declare(strict_types=1);

use Saloon\Http\Response;
use Saloon\Http\PendingRequest;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Helpers\MiddlewarePipeline;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Tests\Fixtures\Requests\ErrorRequest;
use Saloon\Exceptions\DuplicatePipeNameException;

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

test('you can add a named request pipe to the middleware', function () {
    $pipeline = new MiddlewarePipeline;

    $pipeline
        ->onRequest(function (PendingRequest $request) {
            $request->headers()->add('X-Pipe-One', 'Yee-Haw');
        }, false, 'YeeHawPipe');

    $pendingRequest = connector()->createPendingRequest(new UserRequest);
    $pendingRequest = $pipeline->executeRequestPipeline($pendingRequest);

    expect($pendingRequest->headers()->get('X-Pipe-One'))->toEqual('Yee-Haw');
});

test('the named request pipe must be unique', function () {
    $pipeline = new MiddlewarePipeline;

    $pipeline
        ->onRequest(
            callable: function (PendingRequest $request) {
                $request->headers()->add('X-Pipe-One', 'Yee-Haw');
            },
            prepend: false,
            name: 'YeeHawPipe'
        );

    $this->expectException(DuplicatePipeNameException::class);
    $this->expectExceptionMessage('The "YeeHawPipe" pipe already exists on the pipeline');

    $pipeline
        ->onRequest(
            callable: function (PendingRequest $request) {
                $request->headers()->add('X-Pipe-One', 'Yee-Haw');
            },
            prepend: false,
            name: 'YeeHawPipe'
        );
});

test('you can add a named response pipe to the middleware', function () {
    $pipeline = new MiddlewarePipeline;

    $count = 0;

    $pipeline
        ->onResponse(function (Response $response) use (&$count) {
            $count++;
        }, false, 'ResponsePipe');

    $pendingRequest = connector()->createPendingRequest(new UserRequest);
    $response = Response::fromPsrResponse(MockResponse::make()->getPsrResponse(), $pendingRequest);

    $pipeline->executeResponsePipeline($response);

    expect($count)->toBe(1);
});

test('the named response pipe must be unique', function () {
    $pipeline = new MiddlewarePipeline;

    $pipeline
        ->onResponse(function (Response $response) {
            //
        }, false, 'ResponsePipe');

    $this->expectException(DuplicatePipeNameException::class);
    $this->expectExceptionMessage('The "ResponsePipe" pipe already exists on the pipeline');

    $pipeline
        ->onResponse(function (Response $response) {
            //
        }, false, 'ResponsePipe');
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
        }, false, 'response');

    expect($pipelineB->getRequestPipeline()->getPipes())->toBeEmpty();
    expect($pipelineB->getResponsePipeline()->getPipes())->toBeEmpty();

    $pipelineB->merge($pipelineA);

    expect($pipelineB->getRequestPipeline()->getPipes())->toHaveCount(2);
    expect($pipelineB->getResponsePipeline()->getPipes())->toHaveCount(1);
    expect($pipelineA->getRequestPipeline()->getPipes())->toEqual($pipelineB->getRequestPipeline()->getPipes());
    expect($pipelineA->getResponsePipeline()->getPipes())->toEqual($pipelineB->getResponsePipeline()->getPipes());
});

test('when merging a middleware pipeline together if two pipelines exist with the same pipe it throws an exception', function () {
    $pipelineA = new MiddlewarePipeline;
    $pipelineB = new MiddlewarePipeline;

    $pipelineA->onRequest(fn () => null, false, 'howdy');
    $pipelineB->onRequest(fn () => null, false, 'howdy');

    $this->expectException(DuplicatePipeNameException::class);
    $this->expectExceptionMessage('The "howdy" pipe already exists on the pipeline');

    $pipelineA->merge($pipelineB);
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

test('a middleware pipeline is correctly destructed when finished', function (): void {
    /**
     * This is related to wrapping the {@see \Saloon\Helpers\MiddlewarePipeline::onRequest()} and {@see \Saloon\Helpers\MiddlewarePipeline::onResponse()}
     *   callbacks in {@see \Closure}s, for additional, relevant logic.
     * For some reason, this is causing PHP to not destruct things correctly, keeping unused classes intact.
     * Concretely speaking, for Saloon, this means that the Connector will *not* get destructed, and thereby also not the underlying client.
     * Which in turn leaves open file handles until the process terminates.
     */

    $pipelineReference = WeakReference::create($pipeline = new MiddlewarePipeline);
    $pipeline
        ->onRequest(function (PendingRequest $request) {
            // Doesn't really matter.
        })
        ->onResponse(function (PendingRequest $request) {
            // Doesn't really matter.
        });

    expect($pipeline)->toBeInstanceOf(\Saloon\Contracts\MiddlewarePipeline::class)
        ->and($pipeline->getRequestPipeline())->toBeInstanceOf(\Saloon\Contracts\Pipeline::class)
        ->and($pipeline->getRequestPipeline()->getPipes())->toHaveCount(1)
        ->and($pipeline->getResponsePipeline())->toBeInstanceOf(\Saloon\Contracts\Pipeline::class)
        ->and($pipeline->getResponsePipeline()->getPipes())->toHaveCount(1)
        ->and($pipelineReference->get())->toEqual($pipeline);

    unset($pipeline);

    expect($pipelineReference->get())->toBeNull();
});

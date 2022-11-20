<?php

declare(strict_types=1);

use Saloon\Http\PendingRequest;
use Saloon\Http\Responses\Response;
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

    $pendingRequest = (new UserRequest)->createPendingRequest();
    $pendingRequest = $pipeline->executeRequestPipeline($pendingRequest);

    expect($pendingRequest->headers()->get('X-Pipe-One'))->toEqual('Yee-Haw');
    expect($pendingRequest->headers()->get('X-Pipe-Two'))->toEqual('Howdy');
});

test('if a request pipe returns a pending request, we will use that in the next step', function () {
    $pipeline = new MiddlewarePipeline;

    $errorRequest = (new ErrorRequest())->createPendingRequest();

    $pipeline
        ->onRequest(function (PendingRequest $request) use ($errorRequest) {
            $request->headers()->add('X-Pipe-One', 'Yee-Haw');

            return $errorRequest;
        });

    $pendingRequest = (new UserRequest)->createPendingRequest();
    $pendingRequest = $pipeline->executeRequestPipeline($pendingRequest);

    expect($pendingRequest)->toBe($errorRequest);
});

test('if a response pipe returns a response, we will use that in the next step', function () {
    //
})->skip('Until we have mocking working for version 2');

test('you can add a response pipe to the middleware', function () {
    //
})->skip('Until we have mocking working for version 2');

test('you can define a high priority response pipe', function () {
    //
})->skip('Until we have mocking working for version 2');

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

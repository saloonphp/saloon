<?php

use Sammyjo20\Saloon\Helpers\MiddlewarePipeline;
use Sammyjo20\Saloon\Http\PendingSaloonRequest;
use Sammyjo20\Saloon\Http\Responses\SaloonResponse;
use Sammyjo20\Saloon\Tests\Fixtures\Requests\ErrorRequest;
use Sammyjo20\Saloon\Tests\Fixtures\Requests\UserRequest;

test('you can add a request pipe to the middleware', function () {
    $pipeline = new MiddlewarePipeline;

    $pipeline
        ->addRequestPipe(function (PendingSaloonRequest $request) {
            $request->headers()->push('X-Pipe-One', 'Yee-Haw');
        })
        ->addRequestPipe(function (PendingSaloonRequest $request) {
            $request->headers()->push('X-Pipe-Two', 'Howdy');
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
        ->addRequestPipe(function (PendingSaloonRequest $request) use ($errorRequest) {
            $request->headers()->push('X-Pipe-One', 'Yee-Haw');

            return $errorRequest;
        });

    $pendingRequest = (new UserRequest)->createPendingRequest();
    $pendingRequest = $pipeline->executeRequestPipeline($pendingRequest);

    expect($pendingRequest)->toBe($errorRequest);
});

test('you can define a high priority request pipe', function () {
    $pipeline = new MiddlewarePipeline;

    $pipeline
        ->addRequestPipe(function (PendingSaloonRequest $request) {
            $request->headers()->push('X-Pipe-One', 'Yee-Haw');
        })
        ->addRequestPipe(function (PendingSaloonRequest $request) {
            $request->headers()->push('X-Pipe-One', 'Howdy');
        }, true);

    $pendingRequest = (new UserRequest)->createPendingRequest();
    $pendingRequest = $pipeline->executeRequestPipeline($pendingRequest);

    expect($pendingRequest->headers()->get('X-Pipe-One'))->toEqual('Yee-Haw');
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
        ->addRequestPipe(function (PendingSaloonRequest $request) {
            $request->headers()->push('X-Pipe-One', 'Yee-Haw');
        })
        ->addRequestPipe(function (PendingSaloonRequest $request) {
            $request->headers()->push('X-Pipe-One', 'Howdy');
        })
        ->addResponsePipe(function (SaloonResponse $response) {
            return $response->throw();
        });

    expect($pipelineB->getRequestPipeline()->getPipes())->toBeEmpty();
    expect($pipelineB->getResponsePipeline()->getPipes())->toBeEmpty();

    $pipelineB->merge($pipelineA);

    expect($pipelineB->getRequestPipeline()->getPipes())->toHaveCount(2);
    expect($pipelineB->getResponsePipeline()->getPipes())->toHaveCount(1);
});

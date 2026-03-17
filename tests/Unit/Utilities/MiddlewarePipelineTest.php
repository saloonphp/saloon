<?php

declare(strict_types=1);

use Saloon\Data\Pipe;
use Saloon\Http\Response;
use Saloon\Enums\PipeOrder;
use Saloon\Helpers\Pipeline;
use Saloon\Http\PendingRequest;
use GuzzleHttp\Psr7\HttpFactory;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;
use Saloon\Helpers\MiddlewarePipeline;
use Saloon\Tests\Fixtures\Requests\UserRequest;
use Saloon\Tests\Fixtures\Requests\ErrorRequest;
use Saloon\Exceptions\DuplicatePipeNameException;
use Saloon\Tests\Fixtures\Connectors\TestConnector;
use Saloon\Exceptions\Request\FatalRequestException;

describe('Request Middleware', function () {
    test('you can add a pipe to the middleware', function () {
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

    test('you can add a named pipe to the middleware', function () {
        $pipeline = new MiddlewarePipeline;

        $pipeline
            ->onRequest(function (PendingRequest $request) {
                $request->headers()->add('X-Pipe-One', 'Yee-Haw');
            }, 'YeeHawPipe');

        $pipe = $pipeline->getRequestPipeline()->getPipes()[0];

        expect($pipe)->toBeInstanceOf(Pipe::class);
        expect($pipe->name)->toEqual('YeeHawPipe');
        expect($pipe->order)->toBeNull();

        $pendingRequest = connector()->createPendingRequest(new UserRequest);
        $pendingRequest = $pipeline->executeRequestPipeline($pendingRequest);

        expect($pendingRequest->headers()->get('X-Pipe-One'))->toEqual('Yee-Haw');
    });

    test('the named pipe must be unique', function () {
        $pipeline = new MiddlewarePipeline;

        $pipeline
            ->onRequest(
                callable: function (PendingRequest $request) {
                    $request->headers()->add('X-Pipe-One', 'Yee-Haw');
                },
                name: 'YeeHawPipe'
            );

        $this->expectException(DuplicatePipeNameException::class);
        $this->expectExceptionMessage('The "YeeHawPipe" pipe already exists on the pipeline');

        $pipeline
            ->onRequest(
                callable: function (PendingRequest $request) {
                    $request->headers()->add('X-Pipe-One', 'Yee-Haw');
                },
                name: 'YeeHawPipe'
            );
    });

    test('if a pipe returns a pending request, we will use that in the next step', function () {
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

    test('a pipeline is run in order of pipes', function () {
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

    test('a pipe can be added to the top of the pipeline', function () {
        $pipeline = new MiddlewarePipeline;
        $names = [];

        $pipeline
            ->onRequest(function (PendingRequest $request) use (&$names) {
                $names[] = 'Sam';
            })
            ->onRequest(function (PendingRequest $request) use (&$names) {
                $names[] = 'Taylor';
            }, order: PipeOrder::FIRST)
            ->onRequest(function (PendingRequest $request) use (&$names) {
                $names[] = 'Andrew';
            });

        $pendingRequest = connector()->createPendingRequest(new UserRequest);

        $pipeline->executeRequestPipeline($pendingRequest);

        expect($names)->toEqual(['Taylor', 'Sam', 'Andrew']);
    });

    test('a pipe can be added to the bottom of the pipeline', function () {
        $pipeline = new MiddlewarePipeline;
        $names = [];

        $pipeline
            ->onRequest(function (PendingRequest $request) use (&$names) {
                $names[] = 'Sam';
            })
            ->onRequest(function (PendingRequest $request) use (&$names) {
                $names[] = 'Taylor';
            }, order: PipeOrder::LAST)
            ->onRequest(function (PendingRequest $request) use (&$names) {
                $names[] = 'Andrew';
            });

        $pendingRequest = connector()->createPendingRequest(new UserRequest);

        $pipeline->executeRequestPipeline($pendingRequest);

        expect($names)->toEqual(['Sam', 'Andrew', 'Taylor']);
    });
});

describe('Response Middleware', function () {
    test('you can add a named pipe to the middleware', function () {
        $pipeline = new MiddlewarePipeline;

        $count = 0;

        $pipeline
            ->onResponse(function (Response $response) use (&$count) {
                $count++;
            }, 'ResponsePipe');

        $pipe = $pipeline->getResponsePipeline()->getPipes()[0];

        expect($pipe)->toBeInstanceOf(Pipe::class);
        expect($pipe->name)->toEqual('ResponsePipe');
        expect($pipe->order)->toBeNull();

        $factory = new HttpFactory;

        $pendingRequest = connector()->createPendingRequest(new UserRequest);
        $response = Response::fromPsrResponse(MockResponse::make()->createPsrResponse($factory, $factory), $pendingRequest, $pendingRequest->createPsrRequest());

        $pipeline->executeResponsePipeline($response);

        expect($count)->toBe(1);
    });

    test('the named pipe must be unique', function () {
        $pipeline = new MiddlewarePipeline;

        $pipeline
            ->onResponse(function (Response $response) {
                //
            }, 'ResponsePipe');

        $this->expectException(DuplicatePipeNameException::class);
        $this->expectExceptionMessage('The "ResponsePipe" pipe already exists on the pipeline');

        $pipeline
            ->onResponse(function (Response $response) {
                //
            }, 'ResponsePipe');
    });

    test('you can add a pipe to the middleware', function () {
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

    test('if a pipe returns a response, we will use that in the next step', function () {
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

    test('a pipe is run in order of the pipes', function () {
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

    test('a pipe can be added to the top of the pipeline', function () {
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
            }, order: PipeOrder::FIRST)
            ->onResponse(function (Response $response) use (&$names) {
                $names[] = 'Andrew';
            });

        $response = connector()->send(new UserRequest, $mockClient);

        $pipeline->executeResponsePipeline($response);

        expect($names)->toEqual(['Taylor', 'Sam', 'Andrew']);
    });

    test('a pipe can be added to the bottom of the pipeline', function () {
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
            }, order: PipeOrder::LAST)
            ->onResponse(function (Response $response) use (&$names) {
                $names[] = 'Andrew';
            });

        $response = connector()->send(new UserRequest, $mockClient);

        $pipeline->executeResponsePipeline($response);

        expect($names)->toEqual(['Sam', 'Andrew', 'Taylor']);
    });
});

describe('Fatal Middleware', function () {
    test('you can add a pipe to the middleware', function () {
        $pipeline = new MiddlewarePipeline;

        $count = 0;

        $pipeline
            ->onFatalException(function (FatalRequestException $exception) use (&$count) {
                expect($exception)->toBeInstanceOf(FatalRequestException::class);

                $count++;
            })
            ->onFatalException(function (FatalRequestException $exception) use (&$count) {
                expect($exception)->toBeInstanceOf(FatalRequestException::class);
                $count++;
            });

        $connector = new TestConnector('https://saloon.doesnt-exist');
        $request = new UserRequest();

        try {
            $connector->send($request);
        } catch (FatalRequestException $e) {
            $pipeline->executeFatalPipeline($e);
            expect($e)->toBeInstanceOf(FatalRequestException::class);
            expect($count)->toBe(2);
        }
    });

    test('you can add a named pipe to the middleware', function () {
        $pipeline = new MiddlewarePipeline;

        $count = 0;

        $pipeline
            ->onFatalException(function (FatalRequestException $exception) use (&$count) {
                $count++;
            }, 'FatalPipe');

        $pipe = $pipeline->getFatalPipeline()->getPipes()[0];

        expect($pipe)->toBeInstanceOf(Pipe::class);
        expect($pipe->name)->toEqual('FatalPipe');
        expect($pipe->order)->toBeNull();

        $connector = new TestConnector('https://saloon.doesnt-exist');
        $request = new UserRequest();

        try {
            $connector->send($request);
        } catch (FatalRequestException $e) {
            $pipeline->executeFatalPipeline($e);
            expect($e)->toBeInstanceOf(FatalRequestException::class);
            expect($count)->toBe(1);
        }
    });

    test('the named pipe must be unique', function () {
        $pipeline = new MiddlewarePipeline;

        $count = 0;

        $pipeline
            ->onFatalException(
                callable: function (PendingRequest $request) use (&$count) {
                    $count++;
                },
                name: 'YeeHawPipe'
            );

        $this->expectException(DuplicatePipeNameException::class);
        $this->expectExceptionMessage('The "YeeHawPipe" pipe already exists on the pipeline');

        $pipeline
            ->onFatalException(
                callable: function (PendingRequest $request) use (&$count) {
                    $count++;
                },
                name: 'YeeHawPipe'
            );
    });

    test('a pipe is run in order of the pipes', function () {
        $names = [];

        $pipeline = new MiddlewarePipeline;

        $pipeline
            ->onFatalException(function (FatalRequestException $exception) use (&$names) {
                $names[] = 'Sam';
            })
            ->onFatalException(function (FatalRequestException $exception) use (&$names) {
                $names[] = 'Taylor';
            });

        $connector = new TestConnector('https://saloon.doesnt-exist');
        $request = new UserRequest();

        try {
            $connector->send($request);
        } catch (FatalRequestException $e) {
            $pipeline->executeFatalPipeline($e);
            expect($names)->toEqual(['Sam', 'Taylor']);
        }
    });

    test('a pipe can be added to the top of the pipeline', function () {
        $names = [];

        $pipeline = new MiddlewarePipeline;

        $pipeline
            ->onFatalException(function (FatalRequestException $exception) use (&$names) {
                $names[] = 'Sam';
            })
            ->onFatalException(function (FatalRequestException $exception) use (&$names) {
                $names[] = 'Taylor';
            }, order: PipeOrder::FIRST)
            ->onFatalException(function (FatalRequestException $exception) use (&$names) {
                $names[] = 'Andrew';
            });

        $connector = new TestConnector('https://saloon.doesnt-exist');
        $request = new UserRequest();

        try {
            $connector->send($request);
        } catch (FatalRequestException $e) {
            $pipeline->executeFatalPipeline($e);
            expect($names)->toEqual(['Taylor', 'Sam', 'Andrew']);
        }
    });

    test('a pipe can be added to the bottom of the pipeline', function () {
        $names = [];

        $pipeline = new MiddlewarePipeline;

        $pipeline
            ->onFatalException(function (FatalRequestException $exception) use (&$names) {
                $names[] = 'Sam';
            })
            ->onFatalException(function (FatalRequestException $exception) use (&$names) {
                $names[] = 'Taylor';
            }, order: PipeOrder::LAST)
            ->onFatalException(function (FatalRequestException $exception) use (&$names) {
                $names[] = 'Andrew';
            });

        $connector = new TestConnector('https://saloon.doesnt-exist');
        $request = new UserRequest();

        try {
            $connector->send($request);
        } catch (FatalRequestException $e) {
            $pipeline->executeFatalPipeline($e);
            expect($names)->toEqual(['Sam', 'Andrew', 'Taylor']);
        }
    });
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
        }, 'response');

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

    $pipelineA->onRequest(fn () => null, 'howdy');
    $pipelineB->onRequest(fn () => null, 'howdy');

    $this->expectException(DuplicatePipeNameException::class);
    $this->expectExceptionMessage('The "howdy" pipe already exists on the pipeline');

    $pipelineA->merge($pipelineB);
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
        }, order: PipeOrder::LAST)
        ->onResponse(function (PendingRequest $request) {
            // Doesn't really matter.
        });

    expect($pipeline)->toBeInstanceOf(MiddlewarePipeline::class)
        ->and($pipeline->getRequestPipeline())->toBeInstanceOf(Pipeline::class)
        ->and($pipeline->getRequestPipeline()->getPipes())->toHaveCount(1)
        ->and($pipeline->getResponsePipeline())->toBeInstanceOf(Pipeline::class)
        ->and($pipeline->getResponsePipeline()->getPipes())->toHaveCount(2)
        ->and($pipelineReference->get())->toEqual($pipeline);

    unset($pipeline);

    expect($pipelineReference->get())->toBeNull();
});

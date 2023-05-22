<?php

namespace Saloon\Traits\PendingRequest;

use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Promise\RejectedPromise;
use Saloon\Contracts\Response as ResponseContract;
use Saloon\Contracts\FakeResponse;
use Saloon\Exceptions\PendingRequestException;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Response;
use Throwable;

trait CreatesFakeResponses
{
    /**
     * Create the fake response
     *
     * @return PromiseInterface|ResponseContract
     * @throws PendingRequestException
     * @throws Throwable
     */
    public function createFakeResponse(): PromiseInterface|Response
    {
        $fakeResponse = $this->getFakeResponse();

        if (! $fakeResponse instanceof FakeResponse) {
            throw new PendingRequestException('Unable to create fake response because there is no fake response data.');
        }

        $response = $fakeResponse->createPsrResponse(
            responseFactory: $this->factoryCollection->responseFactory,
            streamFactory: $this->factoryCollection->streamFactory,
        );

        // Check if the FakeResponse throws an exception. If the request is
        // asynchronous, then we should allow the promise handler to deal with the exception.

        $exception = $fakeResponse->getException($this);

        if ($exception instanceof Throwable && $this->isAsynchronous() === false) {
            throw $exception;
        }

        // Let's create our response!

        /** @var class-string<\Saloon\Contracts\Response> $responseClass */
        $responseClass = $this->getResponseClass();

        $response = $responseClass::fromPsrResponse(
            psrResponse: $response,
            pendingRequest: $this,
            senderException: $exception
        );

        // When the FakeResponse is specifically a MockResponse then
        // we will record the response, and we'll set the "isMocked" property
        // on the response.

        if ($fakeResponse instanceof MockResponse) {
            $this->getMockClient()?->recordResponse($response);
            $response->setMocked(true);
        }

        // We'll also set the FakeResponse on the response
        // for people to access it if they need to.

        $response->setFakeResponse($fakeResponse);

        // We'll return the synchronous response directly

        if ($this->delay()->isNotEmpty()) {
            usleep($this->delay()->get() * 1000);
        }

        if ($this->isAsynchronous() === false) {
            return $response;
        }

        // When mocking asynchronous requests we need to wrap the response
        // in FulfilledPromise or RejectedPromise depending on if the
        // response has an exception.

        $exception ??= $response->toException();

        return $exception instanceof Throwable ? new RejectedPromise($exception) : new FulfilledPromise($response);
    }
}

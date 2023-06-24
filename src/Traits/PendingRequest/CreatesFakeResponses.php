<?php

declare(strict_types=1);

namespace Saloon\Traits\PendingRequest;

use Throwable;
use Saloon\Http\Response;
use Saloon\Contracts\FakeResponse;
use Saloon\Http\Faking\MockResponse;
use GuzzleHttp\Promise\RejectedPromise;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;
use Saloon\Exceptions\PendingRequestException;
use Saloon\Contracts\Response as ResponseContract;

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

        // Let's create our response!

        $response = $fakeResponse->createPsrResponse(
            responseFactory: $this->factoryCollection->responseFactory,
            streamFactory: $this->factoryCollection->streamFactory,
        );

        /** @var class-string<\Saloon\Contracts\Response> $responseClass */
        $responseClass = $this->getResponseClass();

        $response = $responseClass::fromPsrResponse(
            psrResponse: $response,
            pendingRequest: $this,
            psrRequest: $this->createPsrRequest(),
        );

        $response->setFakeResponse($fakeResponse);

        // When the FakeResponse is specifically a MockResponse then we will
        // record the response, and we'll set the "isMocked" property on
        // the response.

        if ($fakeResponse instanceof MockResponse) {
            $this->getMockClient()?->recordResponse($response);
            $response->setMocked(true);
        }

        if ($this->isAsynchronous()) {
            // When mocking asynchronous requests we need to wrap the response
            // in FulfilledPromise or RejectedPromise depending on if the
            // response has an exception.

            $exception ??= $response->toException();

            return is_null($exception) ? new FulfilledPromise($response) : new RejectedPromise($exception);
        }

        return $response;
    }
}

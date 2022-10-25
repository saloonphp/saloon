<?php

namespace Sammyjo20\Saloon\Traits;

use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\Promise;
use ReflectionException;
use GuzzleHttp\Promise\PromiseInterface;
use Sammyjo20\Saloon\Clients\MockClient;
use Sammyjo20\Saloon\Exceptions\DataBagException;
use Sammyjo20\Saloon\Exceptions\PendingSaloonRequestException;
use Sammyjo20\Saloon\Exceptions\SaloonException;
use Sammyjo20\Saloon\Exceptions\SaloonInvalidConnectorException;
use Sammyjo20\Saloon\Exceptions\SaloonInvalidResponseClassException;
use Sammyjo20\Saloon\Http\MockResponse;
use Sammyjo20\Saloon\Http\Responses\SimulatedResponse;
use Sammyjo20\Saloon\Http\Responses\SaloonResponse;
use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Interfaces\SaloonResponseInterface;

trait SendsRequests
{
    /**
     * Send the request synchronously.
     *
     * @param SaloonRequest $request
     * @param MockClient|null $mockClient
     * @param bool $asynchronous
     * @return SaloonResponse|PromiseInterface
     * @throws ReflectionException
     * @throws DataBagException
     * @throws PendingSaloonRequestException
     * @throws SaloonInvalidConnectorException
     * @throws SaloonInvalidResponseClassException
     */
    public function send(SaloonRequest $request, MockClient $mockClient = null, bool $asynchronous = false): SaloonResponseInterface|PromiseInterface
    {
        // We'll set the request's connector to the current instance.

        $request->setConnector($this);

        // Now we'll create the pending request

        $pendingRequest = $request->createPendingRequest($mockClient);

        // If the pending request has a mock response then we will create
        // a fake response. Otherwise, we will send the real request
        // with the sender.

        if ($pendingRequest->hasMockResponse()) {
            $response = new SimulatedResponse($pendingRequest);
        } else {
            $response = $this->sender()->sendRequest($pendingRequest, $asynchronous);
        }

        // If the request was asynchronous we need to execute the middleware
        // pipeline as the first step in our promise.

        if ($asynchronous === true) {
            $response = $response instanceof SimulatedResponse ? new FulfilledPromise($response) : $response;

            return $response->then(fn(SaloonResponse $response) => $pendingRequest->executeResponsePipeline($response));
        }

        // Otherwise, we'll just return the result of the response pipeline.

        return $pendingRequest->executeResponsePipeline($response);
    }

    /**
     * Send a request asynchronously
     *
     * @param SaloonRequest $request
     * @param MockClient|null $mockClient
     * @return PromiseInterface
     * @throws DataBagException
     * @throws PendingSaloonRequestException
     * @throws ReflectionException
     * @throws SaloonInvalidConnectorException
     * @throws SaloonInvalidResponseClassException
     */
    public function sendAsync(SaloonRequest $request, MockClient $mockClient = null): PromiseInterface
    {
        return $this->send($request, $mockClient, true);
    }
}

<?php

namespace Sammyjo20\Saloon\Actions;

use Sammyjo20\Saloon\Http\MockResponse;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;
use Sammyjo20\Saloon\Contracts\SaloonResponse;
use Sammyjo20\Saloon\Http\PendingSaloonRequest;
use Sammyjo20\Saloon\Http\Responses\SimulatedResponse;

class SendRequest
{
    /**
     * Constructor
     *
     * @param PendingSaloonRequest $pendingRequest
     * @param bool $asynchronous
     */
    public function __construct(protected PendingSaloonRequest $pendingRequest, protected bool $asynchronous = false)
    {
        //
    }

    /**
     * Execute the action
     *
     * @return SaloonResponse|PromiseInterface
     */
    public function execute(): SaloonResponse|PromiseInterface
    {
        $pendingRequest = $this->pendingRequest;

        $response = $pendingRequest->hasSimulatedResponseData() ? $this->createSimulatedResponse() : $this->createResponse();

        if (! $response instanceof PromiseInterface) {
            return $pendingRequest->executeResponsePipeline($response);
        }

        return $response->then(fn (SaloonResponse $response) => $pendingRequest->executeResponsePipeline($response));
    }

    /**
     * Process a simulated response
     *
     * @return SaloonResponse|PromiseInterface
     */
    protected function createSimulatedResponse(): SaloonResponse|PromiseInterface
    {
        $pendingRequest = $this->pendingRequest;
        $simulatedResponseData = $pendingRequest->getSimulatedResponseData();

        $response = new SimulatedResponse($pendingRequest, $simulatedResponseData);

        if ($simulatedResponseData instanceof MockResponse) {
            $pendingRequest->getMockClient()?->recordResponse($response);
            $response->setIsMocked(true);
        }

        if ($this->asynchronous === true) {
            $response = new FulfilledPromise($response);
        }

        return $response;
    }

    /**
     * Send the request and create a response
     *
     * @return SaloonResponse|PromiseInterface
     */
    protected function createResponse(): SaloonResponse|PromiseInterface
    {
        $pendingRequest = $this->pendingRequest;

        return $pendingRequest->getSender()->sendRequest($pendingRequest, $this->asynchronous);
    }
}

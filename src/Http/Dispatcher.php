<?php declare(strict_types=1);

namespace Saloon\Http;

use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;
use Saloon\Contracts\Response as ResponseContract;
use Saloon\Exceptions\DispatcherException;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\Faking\SimulatedResponsePayload;
use Saloon\Http\Responses\Response;

class Dispatcher
{
    /**
     * Constructor
     *
     * @param PendingRequest $pendingRequest
     * @param bool $asynchronous
     */
    public function __construct(protected PendingRequest $pendingRequest, protected bool $asynchronous = false)
    {
        //
    }

    /**
     * Execute the action
     *
     * @return ResponseContract|PromiseInterface
     */
    public function execute(): ResponseContract|PromiseInterface
    {
        $pendingRequest = $this->pendingRequest;

        // Let's start by checking if the pending request needs to make a request.
        // If SimulatedResponsePayload has been set on the instance than we need
        // to create the SimulatedAbstractResponse and return that. Otherwise, we
        // will send a real request to the sender.

        $response = $pendingRequest->hasSimulatedResponsePayload() ? $this->createSimulatedResponse() : $this->createResponse();

        // Next we will need to run the response pipeline. If the response
        // is a AbstractResponse we can run it directly, but if it is
        // a PromiseInterface we need to add a step to execute
        // the response pipeline.

        if ($response instanceof ResponseContract) {
            return $pendingRequest->executeResponsePipeline($response);
        }

        return $response->then(fn (ResponseContract $response) => $pendingRequest->executeResponsePipeline($response));
    }

    /**
     * Process a simulated response
     *
     * @return ResponseContract|PromiseInterface
     */
    protected function createSimulatedResponse(): ResponseContract|PromiseInterface
    {
        $pendingRequest = $this->pendingRequest;
        $simulatedResponsePayload = $pendingRequest->getSimulatedResponsePayload();

        // When the pending request instance has SimulatedResponsePayload it means
        // we shouldn't send a real request. We can use the custom response
        // SimulatedAbstractResponse to parse this payload ad convert it into
        // a AbstractResponse implementation.

        $response = new Response($pendingRequest, $simulatedResponsePayload->getPsrResponse());

        // When the SimulatedResponsePayload is specifically a MockResponse then
        // we will record the response, and we'll set the "isMocked" property
        // on the response.

        if ($simulatedResponsePayload instanceof MockResponse) {
            $pendingRequest->getMockClient()?->recordResponse($response);
        }

        // When mocking asynchronous requests we need to wrap the response
        // in FulfilledPromise to act like a real response.

        if ($this->asynchronous === true) {
            $response = new FulfilledPromise($response);
        }

        return $response;
    }

    /**
     * Send the request and create a response
     *
     * @return ResponseContract|PromiseInterface
     */
    protected function createResponse(): ResponseContract|PromiseInterface
    {
        // The PendingRequest will get the sender from the connector
        // for example the GuzzleSender, and it will instantiate it if
        // it does not exist already. Then it will run sendRequest.

        $pendingRequest = $this->pendingRequest;

        return $pendingRequest->getSender()->sendRequest($pendingRequest, $this->asynchronous);
    }
}

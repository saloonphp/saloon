<?php

declare(strict_types=1);

namespace Saloon\Http;

use Saloon\Contracts\Sender;
use Saloon\Contracts\Response;
use Saloon\Contracts\PendingRequest;
use GuzzleHttp\Promise\PromiseInterface;
use Saloon\Http\Senders\SimulatedSender;
use Saloon\Contracts\Dispatcher as DispatcherContract;

class Dispatcher implements DispatcherContract
{
    /**
     * Constructor
     *
     * @param \Saloon\Contracts\PendingRequest $pendingRequest
     */
    public function __construct(protected PendingRequest $pendingRequest)
    {
        //
    }

    /**
     * Execute the action
     *
     * @return \Saloon\Contracts\Response|PromiseInterface
     */
    public function execute(): Response|PromiseInterface
    {
        $pendingRequest = $this->pendingRequest;

        // Let's start by checking if the pending request needs to make a request.
        // If SimulatedResponsePayload has been set on the instance than we need
        // to create the SimulatedResponse and return that. Otherwise, we
        // will send a real request to the sender.

        $response = $this->getSender()->sendRequest($pendingRequest, $pendingRequest->isAsynchronous());

        // Next we will need to run the response pipeline. If the response
        // is a Response we can run it directly, but if it is
        // a PromiseInterface we need to add a step to execute
        // the response pipeline.

        if ($response instanceof Response) {
            return $pendingRequest->executeResponsePipeline($response);
        }

        return $response->then(fn (Response $response) => $pendingRequest->executeResponsePipeline($response));
    }

    /**
     * Get the sender
     *
     * @return \Saloon\Contracts\Sender
     */
    protected function getSender(): Sender
    {
        $pendingRequest = $this->pendingRequest;

        return $pendingRequest->hasSimulatedResponsePayload() ? new SimulatedSender : $pendingRequest->getSender();
    }
}

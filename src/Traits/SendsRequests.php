<?php

namespace Sammyjo20\Saloon\Traits;

use GuzzleHttp\Promise\PromiseInterface;
use ReflectionException;
use Sammyjo20\Saloon\Clients\MockClient;
use Sammyjo20\Saloon\Exceptions\SaloonException;
use Sammyjo20\Saloon\Exceptions\SaloonNoMockResponsesProvidedException;
use Sammyjo20\Saloon\Http\Responses\SaloonResponse;

trait SendsRequests
{
    /**
     * Send the request synchronously.
     *
     * @param MockClient|null $mockClient
     * @param bool $asynchronous
     * @return SaloonResponse|PromiseInterface
     * @throws SaloonException|ReflectionException
     */
    public function send(MockClient $mockClient = null, bool $asynchronous = false): SaloonResponse|PromiseInterface
    {
        $pendingRequest = $this->createPendingRequest();
        $requestSender = $pendingRequest->getSender();

        // If a mock client has been provided, we will only set it for this request.

        if ($mockClient instanceof MockClient) {
            $pendingRequest->setMockClient($mockClient);
        }

        // If there is a mock client found, we should attempt to guess the next response
        // and then run the "handleMockResponse" method on the sender.

        if ($pendingRequest->isMocking()) {
            $mockResponse = $pendingRequest->getMockClient()->guessNextResponse($pendingRequest->getRequest());

            return $requestSender->handleMockResponse($mockResponse, $pendingRequest, $asynchronous);
        }

        // If any of the middleware have registered early responses, we should
        // process this response right away.

        if ($pendingRequest->hasEarlyResponse()) {
            return $requestSender->handleResponse($pendingRequest, $pendingRequest->getEarlyResponse(), $asynchronous);
        }

        // 🚀 ... 🌑 ... 💫

        return $requestSender->sendRequest($pendingRequest, $asynchronous);
    }

    /**
     * Send a request asynchronously
     *
     * @param MockClient|null $mockClient
     * @return PromiseInterface
     * @throws ReflectionException
     * @throws SaloonException
     */
    public function sendAsync(MockClient $mockClient = null): PromiseInterface
    {
        return $this->send($mockClient, true);
    }
}

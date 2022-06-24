<?php

namespace Sammyjo20\Saloon\Traits;

use GuzzleHttp\Promise\PromiseInterface;
use Sammyjo20\Saloon\Clients\MockClient;
use Sammyjo20\Saloon\Http\Responses\SaloonResponse;

trait SendsRequests
{
    /**
     * Send the request synchronously.
     *
     * @param MockClient|null $mockClient
     * @param bool $asynchronous
     * @return SaloonResponse|PromiseInterface
     * @throws \ReflectionException
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonException
     */
    public function send(MockClient $mockClient = null, bool $asynchronous = false): SaloonResponse|PromiseInterface
    {
        if ($mockClient instanceof MockClient) {
            $this->withMockClient($mockClient);
        }

        // 🚀 ... 🌑 ... 💫

        $pendingRequest = $this->createPendingRequest();
        $requestSender = $pendingRequest->getRequestSender();

        // If any of the middleware have registered early responses, like mocking or caching,
        // we should process this response right away.

        if ($pendingRequest->hasEarlyResponse()) {
            return $requestSender->handleResponse($pendingRequest, $pendingRequest->getEarlyResponse(), $asynchronous);
        }

        return $requestSender->sendRequest($pendingRequest, $asynchronous);
    }

    /**
     * Send a request asynchronously
     *
     * @param MockClient|null $mockClient
     * @return PromiseInterface
     * @throws \ReflectionException
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonException
     */
    public function sendAsync(MockClient $mockClient = null): PromiseInterface
    {
        return $this->send($mockClient, true);
    }
}

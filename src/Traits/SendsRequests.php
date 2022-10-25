<?php

namespace Sammyjo20\Saloon\Traits;

use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\Promise;
use ReflectionException;
use GuzzleHttp\Promise\PromiseInterface;
use Sammyjo20\Saloon\Clients\MockClient;
use Sammyjo20\Saloon\Exceptions\SaloonException;
use Sammyjo20\Saloon\Http\Responses\FakeResponse;
use Sammyjo20\Saloon\Http\Responses\SaloonResponse;
use Sammyjo20\Saloon\Interfaces\SaloonResponseInterface;

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
    public function send(MockClient $mockClient = null, bool $asynchronous = false): SaloonResponseInterface|PromiseInterface
    {
        $pendingRequest = $this->createPendingRequest($mockClient);

        // We'll now check if a mock response exists on the request, if it
        // does, it means that we shouldn't dispatch the real request.

        if ($pendingRequest->hasMockResponse()) {
            $response = new FakeResponse($pendingRequest);

            if ($asynchronous === true) {
                return new FulfilledPromise($response);
            }

            return $response;
        }

        // Otherwise we will send the request...

        // 🚀 ... 🌑 ... 💫

        return $pendingRequest->getSender()->sendRequest($pendingRequest, $asynchronous);
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

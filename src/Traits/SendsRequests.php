<?php

namespace Sammyjo20\Saloon\Traits;

use Sammyjo20\Saloon\Clients\MockClient;
use Sammyjo20\Saloon\Http\SaloonResponse;
use Sammyjo20\Saloon\Http\Senders\GuzzleSender;

trait SendsRequests
{
    /**
     * Send the request synchronously.
     *
     * @param MockClient|null $mockClient
     * @return SaloonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \ReflectionException
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonException
     */
    public function send(MockClient $mockClient = null): SaloonResponse
    {
        // TODO: Proper async requests...

        if ($mockClient instanceof MockClient) {
            $this->withMockClient($mockClient);
        }

        // 🚀 ... 🌑 ... 💫

        return (new GuzzleSender())->handle($this->createPendingRequest());
    }
}

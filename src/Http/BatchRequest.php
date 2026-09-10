<?php

namespace Saloon\Http;

use Saloon\Contracts\ArrayStore as ArrayStoreContract;
use Saloon\Repositories\ArrayStore;

abstract class BatchRequest
{
    /**
     * Request Headers
     */
    protected ArrayStoreContract $requests;

    /**
     * Get all requests.
     *
     * @return ArrayStoreContract
     */
    public function getRequests(): ArrayStoreContract
    {
        return $this->requests ??= new ArrayStore($this->defaultRequests());
    }

    /**
     * Add a request dynamically.
     *
     * @param Request $request
     * @return void
     */
    public function addRequest(Request $request): void
    {
        $this->requests->add(uniqid('request_', true), $request);
    }

    /**
     * Get default requests.
     *
     * @return array<Request>
     */
    protected function defaultRequests(): array
    {
        return [];
    }

    /**
     * Process the batch of responses.
     *
     * @param array<Response> $responses
     * @return mixed
     */
    abstract public function processResponses(array $responses): mixed;
}

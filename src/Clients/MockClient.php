<?php

namespace Sammyjo20\Saloon\Clients;

use Sammyjo20\Saloon\Exceptions\SaloonNoMockResponsesProvidedException;
use Sammyjo20\Saloon\Http\MockResponse;

class MockClient
{
    /**
     * @var array
     */
    protected array $responses = [];

    /**
     * @param array $responses
     * @throws SaloonNoMockResponsesProvidedException
     */
    public function __construct(array $responses = [])
    {
        $this->addResponses($responses);
    }

    /**
     * Process the incoming responses
     *
     * @param array $responses
     * @return array
     */
    public function addResponses(array $responses): self
    {
        $responses = array_filter($responses, function ($response) {
            return $response instanceof MockResponse;
        });

        $this->responses = array_merge($this->responses, $responses);

        return $this;
    }

    /**
     * Add another response to the stack
     *
     * @param MockResponse $response
     * @return $this
     */
    public function addResponse(MockResponse $response): self
    {
        $this->responses[] = $response;

        return $this;
    }

    /**
     * Pull out a response from the client.
     *
     * @return mixed
     */
    public function getNextResponse(): mixed
    {
        return array_shift($this->responses);
    }

    /**
     * Check if the responses are empty
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->responses);
    }
}

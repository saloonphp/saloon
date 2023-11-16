<?php

declare(strict_types=1);

namespace Saloon\Traits;

use Saloon\Http\Faking\Fixture;
use Saloon\Http\PendingRequest;
use Saloon\Contracts\HasFakeData;
use Saloon\Http\Faking\MockClient;
use Saloon\Http\Faking\MockResponse;

trait HasMockClient
{
    /**
     * Mock Client
     */
    protected ?MockClient $mockClient = null;

    /**
     * Specify a mock client.
     *
     * @return $this
     */
    public function withMockClient(MockClient $mockClient): static
    {
        $this->mockClient = $mockClient;

        return $this;
    }

    /**
     * Creates a MockResponse instance from the given value if
     * it is a string or an array. Otherwise, it returns the
     * value as is.
     */
    private function prepareMockResponse(mixed $value): MockResponse|Fixture|callable
    {
        if (is_array($value) || is_string($value)) {
            return new MockResponse($value);
        }

        return $value;
    }

    /**
     * Mocks the given requests with the given responses, or with the
     * mocked response from the 'getFakeData()' method if the
     * request has implemented the HasFakeData interface.
     *
     * @param array<array-key|class-string, mixed> $requestMocks
     */
    public function withRequestMocks(array $requestMocks): static
    {
        $responses = collect($requestMocks)->mapWithKeys(function (mixed $value, int|string $key) {
            // Something like: [UserRequest::class => MockResponse::fixture('user')];
            // Or URL mocking: ['/user' => ['id' => 123];
            if (! is_numeric($key)) {
                return [$key => $this->prepareMockResponse($value)];
            }

            // Just the Request class name or the URL. Retrieve the fake data from the request.
            return [$value => function (PendingRequest $pendingRequest) {
                $request = $pendingRequest->getRequest();

                return $request instanceof HasFakeData
                    ? $this->prepareMockResponse($request->getFakeData($pendingRequest))
                    : MockResponse::make();
            }];
        })->all();

        if ($this->hasMockClient()) {
            $this->getMockClient()->addResponses($responses);

            return $this;
        }

        return $this->withMockClient(new MockClient($responses));
    }

    /**
     * Get the mock client.
     */
    public function getMockClient(): ?MockClient
    {
        return $this->mockClient;
    }

    /**
     * Determine if the instance has a mock client
     */
    public function hasMockClient(): bool
    {
        return $this->mockClient instanceof MockClient;
    }
}

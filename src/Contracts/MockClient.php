<?php declare(strict_types=1);

namespace Saloon\Contracts;

use Saloon\Http\Request;
use Saloon\Http\Faking\Fixture;
use Saloon\Http\Faking\MockResponse;
use Saloon\Http\PendingRequest;
use Saloon\Exceptions\SaloonNoMockResponseFoundException;
use Saloon\Exceptions\SaloonInvalidMockResponseCaptureMethodException;

interface MockClient
{
    /**
     * Store the mock responses in the correct places.
     *
     * @param array $responses
     * @return void
     * @throws SaloonInvalidMockResponseCaptureMethodException
     */
    public function addResponses(array $responses): void;

    /**
     * Add a mock response to the client
     *
     * @param MockResponse|Fixture|callable $response
     * @param string|null $captureMethod
     * @return void
     * @throws SaloonInvalidMockResponseCaptureMethodException
     */
    public function addResponse(MockResponse|Fixture|callable $response, ?string $captureMethod = null): void;

    /**
     * Get the next response in the sequence
     *
     * @return mixed
     */
    public function getNextFromSequence(): mixed;

    /**
     * Guess the next response based on the request.
     *
     * @param PendingRequest $pendingRequest
     * @return MockResponse|Fixture
     * @throws SaloonNoMockResponseFoundException
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonInvalidConnectorException
     */
    public function guessNextResponse(PendingRequest $pendingRequest): MockResponse|Fixture;

    /**
     * Check if the responses are empty.
     *
     * @return bool
     */
    public function isEmpty(): bool;

    /**
     * Record a response.
     *
     * @param SaloonResponse $response
     * @return void
     */
    public function recordResponse(SaloonResponse $response): void;

    /**
     * Get all the recorded responses
     *
     * @return array
     */
    public function getRecordedResponses(): array;

    /**
     * Get the last request that the mock manager sent.
     *
     * @return Request|null
     */
    public function getLastRequest(): ?Request;

    /**
     * Get the last response that the mock manager sent.
     *
     * @return SaloonResponse|null
     */
    public function getLastResponse(): ?SaloonResponse;

    /**
     * Assert that a given request was sent.
     *
     * @param string|callable $value
     * @return void
     * @throws \ReflectionException|\Sammyjo20\Saloon\Exceptions\SaloonInvalidConnectorException
     */
    public function assertSent(string|callable $value): void;

    /**
     * Assert that a given request was not sent.
     *
     * @param string|callable $request
     * @return void
     * @throws \ReflectionException|\Sammyjo20\Saloon\Exceptions\SaloonInvalidConnectorException
     */
    public function assertNotSent(string|callable $request): void;

    /**
     * Assert JSON data was sent
     *
     * @param string $request
     * @param array $data
     * @return void
     * @throws \ReflectionException|\Sammyjo20\Saloon\Exceptions\SaloonInvalidConnectorException
     */
    public function assertSentJson(string $request, array $data): void;

    /**
     * Assert that nothing was sent.
     *
     * @return void
     */
    public function assertNothingSent(): void;

    /**
     * Assert a request count has been met.
     *
     * @param int $count
     * @return void
     */
    public function assertSentCount(int $count): void;

    /**
     * Assert a given request was sent.
     *
     * @param string $request
     * @return SaloonResponse|null
     */
    public function findResponseByRequest(string $request): ?SaloonResponse;

    /**
     * Find a request that matches a given url pattern
     *
     * @param string $url
     * @return SaloonResponse|null
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonInvalidConnectorException
     */
    public function findResponseByRequestUrl(string $url): ?SaloonResponse;
}

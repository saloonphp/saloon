<?php

declare(strict_types=1);

namespace Saloon\Contracts;

use Saloon\Http\Faking\Fixture;
use Saloon\Http\Faking\MockResponse;

interface MockClient
{
    /**
     * Store the mock responses in the correct places.
     *
     * @param array<\Saloon\Http\Faking\MockResponse|\Saloon\Http\Faking\Fixture|callable> $responses
     */
    public function addResponses(array $responses): void;

    /**
     * Add a mock response to the client
     */
    public function addResponse(MockResponse|Fixture|callable $response, ?string $captureMethod = null): void;

    /**
     * Get the next response in the sequence
     */
    public function getNextFromSequence(): mixed;

    /**
     * Guess the next response based on the request.
     */
    public function guessNextResponse(PendingRequest $pendingRequest): MockResponse|Fixture;

    /**
     * Check if the responses are empty.
     */
    public function isEmpty(): bool;

    /**
     * Record a response.
     */
    public function recordResponse(Response $response): void;

    /**
     * Get all the recorded responses
     *
     * @return array<\Saloon\Contracts\Response>
     */
    public function getRecordedResponses(): array;

    /**
     * Get the last request that the mock manager sent.
     */
    public function getLastRequest(): ?Request;

    /**
     * Get the last request that the mock manager sent.
     */
    public function getLastPendingRequest(): ?PendingRequest;

    /**
     * Get the last response that the mock manager sent.
     */
    public function getLastResponse(): ?Response;

    /**
     * Assert that a given request was sent.
     */
    public function assertSent(string|callable $value): void;

    /**
     * Assert that a given request was not sent.
     */
    public function assertNotSent(string|callable $request): void;

    /**
     * Assert JSON data was sent
     *
     * @param array<array-key, mixed> $data
     */
    public function assertSentJson(string $request, array $data): void;

    /**
     * Assert that nothing was sent.
     */
    public function assertNothingSent(): void;

    /**
     * Assert a request count has been met.
     */
    public function assertSentCount(int $count): void;

    /**
     * Assert a given request was sent.
     */
    public function findResponseByRequest(string $request): ?Response;

    /**
     * Find a request that matches a given url pattern
     */
    public function findResponseByRequestUrl(string $url): ?Response;
}

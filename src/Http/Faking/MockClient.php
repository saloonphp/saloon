<?php

declare(strict_types=1);

namespace Saloon\Http\Faking;

use ReflectionClass;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Http\Connector;
use Saloon\Helpers\Helpers;
use Saloon\Helpers\URLHelper;
use Saloon\Http\PendingRequest;
use PHPUnit\Framework\Assert as PHPUnit;
use Saloon\Exceptions\NoMockResponseFoundException;
use Saloon\Exceptions\InvalidMockResponseCaptureMethodException;

class MockClient
{
    /**
     * Collection of all the responses that will be sequenced.
     *
     * @var array<\Saloon\Http\Faking\MockResponse|\Saloon\Http\Faking\Fixture|callable>
     */
    protected array $sequenceResponses = [];

    /**
     * Collection of responses used only when a connector is called.
     *
     * @var array<\Saloon\Http\Faking\MockResponse|\Saloon\Http\Faking\Fixture|callable>
     */
    protected array $connectorResponses = [];

    /**
     * Collection of responses used only when a request is called.
     *
     * @var array<\Saloon\Http\Faking\MockResponse|\Saloon\Http\Faking\Fixture|callable>
     */
    protected array $requestResponses = [];

    /**
     * Collection of responses that will run when the request is matched.
     *
     * @var array<\Saloon\Http\Faking\MockResponse|\Saloon\Http\Faking\Fixture|callable>
     */
    protected array $urlResponses = [];

    /**
     * Collection of all the recorded responses.
     *
     * @var array<\Saloon\Http\Faking\MockResponse|\Saloon\Http\Faking\Fixture|callable>
     */
    protected array $recordedResponses = [];

    /**
     * Global Mock Client
     *
     * Use MockClient::global() to register a global mock client
     */
    protected static ?MockClient $globalMockClient = null;

    /**
     * Constructor
     *
     * @param array<\Saloon\Http\Faking\MockResponse|\Saloon\Http\Faking\Fixture|callable> $mockData
     */
    public function __construct(array $mockData = [])
    {
        $this->addResponses($mockData);
    }

    /**
     * Store the mock responses in the correct places.
     *
     * @param array<\Saloon\Http\Faking\MockResponse|\Saloon\Http\Faking\Fixture|callable> $responses
     */
    public function addResponses(array $responses): void
    {
        foreach ($responses as $key => $response) {
            if (is_int($key)) {
                $key = null;
            }

            $this->addResponse($response, $key);
        }
    }

    /**
     * Add a mock response to the client
     */
    public function addResponse(MockResponse|Fixture|callable $response, ?string $captureMethod = null): void
    {
        if (is_null($captureMethod)) {
            $this->sequenceResponses[] = $response;

            return;
        }

        if (! is_string($captureMethod)) {
            throw new InvalidMockResponseCaptureMethodException;
        }

        // Let's detect if the capture method is either a connector or
        // a request. If so we'll put them in their designated arrays.

        if ($captureMethod && class_exists($captureMethod)) {
            $reflection = new ReflectionClass($captureMethod);

            if ($reflection->isSubclassOf(Connector::class)) {
                $this->connectorResponses[$captureMethod] = $response;

                return;
            }

            if ($reflection->isSubclassOf(Request::class)) {
                $this->requestResponses[$captureMethod] = $response;

                return;
            }
        }

        // Otherwise, the keys must be a URL.

        $this->urlResponses[$captureMethod] = $response;
    }

    /**
     * Get the next response in the sequence
     */
    public function getNextFromSequence(): mixed
    {
        return array_shift($this->sequenceResponses);
    }

    /**
     * Guess the next response based on the request.
     *
     * @throws \Saloon\Exceptions\NoMockResponseFoundException
     */
    public function guessNextResponse(PendingRequest $pendingRequest): MockResponse|Fixture
    {
        $request = $pendingRequest->getRequest();
        $requestClass = get_class($request);

        if (array_key_exists($requestClass, $this->requestResponses)) {
            return $this->mockResponseValue($this->requestResponses[$requestClass], $pendingRequest);
        }

        $connectorClass = get_class($pendingRequest->getConnector());

        if (array_key_exists($connectorClass, $this->connectorResponses)) {
            return $this->mockResponseValue($this->connectorResponses[$connectorClass], $pendingRequest);
        }

        $guessedResponse = $this->guessResponseFromUrl($pendingRequest);

        if (! is_null($guessedResponse)) {
            return $this->mockResponseValue($guessedResponse, $pendingRequest);
        }

        if (empty($this->sequenceResponses)) {
            throw new NoMockResponseFoundException($pendingRequest);
        }

        return $this->mockResponseValue($this->getNextFromSequence(), $pendingRequest);
    }

    /**
     * Guess the response from the URL.
     */
    private function guessResponseFromUrl(PendingRequest $pendingRequest): MockResponse|Fixture|callable|null
    {
        foreach ($this->urlResponses as $url => $response) {
            if (! URLHelper::matches($url, $pendingRequest->getUrl())) {
                continue;
            }

            return $response;
        }

        return null;
    }

    /**
     * Check if the responses are empty.
     */
    public function isEmpty(): bool
    {
        return empty($this->sequenceResponses) && empty($this->connectorResponses) && empty($this->requestResponses) && empty($this->urlResponses);
    }

    /**
     * Record a response.
     */
    public function recordResponse(Response $response): void
    {
        $this->recordedResponses[] = $response;
    }

    /**
     * Get all the recorded responses
     *
     * @return array<\Saloon\Http\Response>
     */
    public function getRecordedResponses(): array
    {
        return $this->recordedResponses;
    }

    /**
     * Get the last request that the mock manager sent.
     */
    public function getLastRequest(): ?Request
    {
        return $this->getLastResponse()?->getPendingRequest()->getRequest();
    }

    /**
     * Get the last request that the mock manager sent.
     */
    public function getLastPendingRequest(): ?PendingRequest
    {
        return $this->getLastResponse()?->getPendingRequest();
    }

    /**
     * Get the last response that the mock manager sent.
     */
    public function getLastResponse(): ?Response
    {
        if (empty($this->recordedResponses)) {
            return null;
        }

        $lastResponse = end($this->recordedResponses);

        reset($this->recordedResponses);

        return $lastResponse;
    }

    /**
     * Assert that a given request was sent.
     */
    public function assertSent(string|callable $value): void
    {
        $result = $this->checkRequestWasSent($value);

        PHPUnit::assertTrue($result, 'An expected request was not sent.');
    }

    /**
     * Assert that a given request was not sent.
     */
    public function assertNotSent(string|callable $request): void
    {
        $result = $this->checkRequestWasNotSent($request);

        PHPUnit::assertTrue($result, 'An unexpected request was sent.');
    }

    /**
     * Assert that given requests were sent in order
     *
     * @param array<\Closure|class-string<Request>|string> $callbacks
     */
    public function assertSentInOrder(array $callbacks): void
    {
        $this->assertSentCount(count($callbacks));

        foreach ($callbacks as $index => $callback) {
            $result = $this->checkRequestWasSent($callback, $index);

            PHPUnit::assertTrue($result, 'An expected request (#'.($index + 1).') was not sent.');
        }
    }

    /**
     * Assert JSON response data was received
     *
     * @deprecated This method will be removed in v4
     *
     * @param array<string, mixed> $data
     */
    public function assertSentJson(string $request, array $data): void
    {
        $this->assertSent(function ($currentRequest, $currentResponse) use ($request, $data) {
            return $currentRequest instanceof $request && $currentResponse->json() === $data;
        });
    }

    /**
     * Assert that nothing was sent.
     */
    public function assertNothingSent(): void
    {
        PHPUnit::assertEmpty($this->getRecordedResponses(), 'Requests were sent.');
    }

    /**
     * Assert a request count has been met.
     */
    public function assertSentCount(int $count, ?string $requestClass = null): void
    {
        if (is_string($requestClass)) {
            $actualCount = $this->getRequestSentCount()[$requestClass] ?? 0;

            PHPUnit::assertEquals($count, $actualCount);

            return;
        }

        PHPUnit::assertCount($count, $this->getRecordedResponses());
    }

    /**
     * Check if a given request was sent
     */
    protected function checkRequestWasSent(string|callable $request, ?int $index = null): bool
    {
        $passed = false;

        if (is_callable($request)) {
            return $this->checkClosureAgainstResponses($request, $index);
        }

        if (is_string($request)) {
            if (class_exists($request) && Helpers::isSubclassOf($request, Request::class)) {
                $passed = $this->findResponseByRequest($request, $index) instanceof Response;
            } else {
                $passed = $this->findResponseByRequestUrl($request, $index) instanceof Response;
            }
        }

        return $passed;
    }

    /**
     * Check if a request has not been sent.
     */
    protected function checkRequestWasNotSent(string|callable $request): bool
    {
        return ! $this->checkRequestWasSent($request);
    }

    /**
     * Assert a given request was sent.
     */
    public function findResponseByRequest(string $request, ?int $index = null): ?Response
    {
        if ($this->checkHistoryEmpty() === true) {
            return null;
        }

        if (! is_null($index)) {
            $recordedResponse = $this->getRecordedResponses()[$index];

            if ($recordedResponse->getPendingRequest()->getRequest() instanceof $request) {
                return $recordedResponse;
            }
        }

        $lastRequest = $this->getLastRequest();

        if ($lastRequest instanceof $request) {
            return $this->getLastResponse();
        }

        foreach ($this->getRecordedResponses() as $recordedResponse) {
            if ($recordedResponse->getPendingRequest()->getRequest() instanceof $request) {
                return $recordedResponse;
            }
        }

        return null;
    }

    /**
     * Find a request that matches a given url pattern
     */
    public function findResponseByRequestUrl(string $url, ?int $index = null): ?Response
    {
        if ($this->checkHistoryEmpty() === true) {
            return null;
        }

        if (! is_null($index)) {
            $response = $this->getRecordedResponses()[$index];
            $pendingRequest = $response->getPendingRequest();

            if (URLHelper::matches($url, $pendingRequest->getUrl())) {
                return $response;
            }

            return null;
        }

        $lastPendingRequest = $this->getLastPendingRequest();

        if ($lastPendingRequest instanceof PendingRequest && URLHelper::matches($url, $lastPendingRequest->getUrl())) {
            return $this->getLastResponse();
        }

        foreach ($this->getRecordedResponses() as $response) {
            $pendingRequest = $response->getPendingRequest();

            if (URLHelper::matches($url, $pendingRequest->getUrl())) {
                return $response;
            }
        }

        return null;
    }

    /**
     * Register a global mock client
     *
     * This will register a global mock client that is available throughout the
     * application's lifecycle. You should destroy the global mock client
     * after each test using MockClient::destroyGlobal().
     *
     * @param array<\Saloon\Http\Faking\MockResponse|\Saloon\Http\Faking\Fixture|callable> $mockData
     */
    public static function global(array $mockData = []): MockClient
    {
        return static::$globalMockClient ??= new static($mockData);
    }

    /**
     * Get the global mock client if it has been registered
     */
    public static function getGlobal(): ?MockClient
    {
        return static::$globalMockClient;
    }

    /**
     * Destroy the global mock client
     */
    public static function destroyGlobal(): void
    {
        static::$globalMockClient = null;
    }

    /**
     * Test if the closure can pass with the history.
     */
    private function checkClosureAgainstResponses(callable $closure, ?int $index = null): bool
    {
        if ($this->checkHistoryEmpty() === true) {
            return false;
        }

        if (! is_null($index)) {
            $response = $this->getRecordedResponses()[$index];
            $request = $response->getPendingRequest()->getRequest();

            return $closure($request, $response);
        }

        // Let's first check if the latest response resolves the callable
        // with a successful result.

        $lastResponse = $this->getLastResponse();

        if ($lastResponse instanceof Response) {
            $passed = $closure($lastResponse->getPendingRequest()->getRequest(), $lastResponse);

            if ($passed === true) {
                return true;
            }
        }

        // If it was not the previous response, we should iterate through each of the
        // responses and break out if we get a match.

        foreach ($this->getRecordedResponses() as $response) {
            $request = $response->getPendingRequest()->getRequest();

            $passed = $closure($request, $response);

            if ($passed === true) {
                return true;
            }
        }

        return false;
    }

    /**
     * Will return true if the history is empty.
     */
    private function checkHistoryEmpty(): bool
    {
        return count($this->recordedResponses) <= 0;
    }

    /**
     * Get the mock value.
     */
    private function mockResponseValue(MockResponse|Fixture|callable $mockable, PendingRequest $pendingRequest): MockResponse|Fixture
    {
        if ($mockable instanceof MockResponse) {
            return $mockable;
        }

        if ($mockable instanceof Fixture) {
            return $mockable;
        }

        return $mockable($pendingRequest);
    }

    /**
     * Get an array of requests recorded with their count
     *
     * @return array<class-string, int>
     */
    private function getRequestSentCount(): array
    {
        $requests = array_map(static function (Response $response) {
            return $response->getRequest()::class;
        }, $this->getRecordedResponses());

        return array_count_values($requests);
    }
}

<?php

namespace Sammyjo20\Saloon\Clients;

use ReflectionClass;
use Sammyjo20\Saloon\Helpers\URLHelper;
use Sammyjo20\Saloon\Http\MockResponse;
use PHPUnit\Framework\Assert as PHPUnit;
use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Http\SaloonResponse;
use Sammyjo20\Saloon\Http\SaloonConnector;
use Sammyjo20\Saloon\Helpers\ReflectionHelper;
use Sammyjo20\Saloon\Exceptions\SaloonNoMockResponseFoundException;
use Sammyjo20\Saloon\Exceptions\SaloonInvalidMockResponseCaptureMethodException;

class BaseMockClient
{
    /**
     * Collection of all the responses that will be sequenced.
     *
     * @var array
     */
    protected array $sequenceResponses = [];

    /**
     * Collection of responses used only when a connector is called.
     *
     * @var array
     */
    protected array $connectorResponses = [];

    /**
     * Collection of responses used only when a request is called.
     *
     * @var array
     */
    protected array $requestResponses = [];

    /**
     * Collection of responses that will run when the request is matched.
     *
     * @var array
     */
    protected array $urlResponses = [];

    /**
     * Collection of all the recorded responses.
     *
     * @var array
     */
    protected array $recordedResponses = [];

    /**
     * @param array $mockData
     * @throws SaloonInvalidMockResponseCaptureMethodException
     */
    public function __construct(array $mockData = [])
    {
        $this->addResponses($mockData);
    }

    /**
     * Store the mock responses in the correct places.
     *
     * @param array $responses
     * @return void
     * @throws SaloonInvalidMockResponseCaptureMethodException
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

    public function addResponse(MockResponse $response, ?string $captureMethod = null): void
    {
        if (is_null($captureMethod)) {
            $this->sequenceResponses[] = $response;

            return;
        }

        if (! is_string($captureMethod)) {
            throw new SaloonInvalidMockResponseCaptureMethodException;
        }

        // Let's detect if the capture method is either a connector or
        // a request. If so we'll put them in their designated arrays.

        if ($captureMethod && class_exists($captureMethod)) {
            $reflection = new ReflectionClass($captureMethod);

            if ($reflection->isSubclassOf(SaloonConnector::class)) {
                $this->connectorResponses[$captureMethod] = $response;

                return;
            }

            if ($reflection->isSubclassOf(SaloonRequest::class)) {
                $this->requestResponses[$captureMethod] = $response;

                return;
            }
        }

        // Otherwise, the keys must be a URL.

        $this->urlResponses[$captureMethod] = $response;
    }

    public function getNextFromSequence(): mixed
    {
        return array_shift($this->sequenceResponses);
    }

    /**
     * Guess the next response based on the request.
     *
     * @param SaloonRequest $request
     * @return MockResponse
     * @throws SaloonNoMockResponseFoundException
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonInvalidConnectorException
     */
    public function guessNextResponse(SaloonRequest $request): MockResponse
    {
        $requestClass = get_class($request);

        if (array_key_exists($requestClass, $this->requestResponses)) {
            return $this->requestResponses[$requestClass];
        }

        $connectorClass = get_class($request->getConnector());

        if (array_key_exists($connectorClass, $this->connectorResponses)) {
            return $this->connectorResponses[$connectorClass];
        }

        $guessedResponse = $this->guessResponseFromUrl($request);

        if (! is_null($guessedResponse)) {
            return $guessedResponse;
        }

        if (empty($this->sequenceResponses)) {
            throw new SaloonNoMockResponseFoundException;
        }

        return $this->getNextFromSequence();
    }

    /**
     * Guess the response from the URL.
     *
     * @param SaloonRequest $request
     * @return MockResponse|null
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonInvalidConnectorException
     */
    private function guessResponseFromUrl(SaloonRequest $request): ?MockResponse
    {
        foreach ($this->urlResponses as $url => $response) {
            if (! URLHelper::matches($url, $request->getFullRequestUrl())) {
                continue;
            }

            return $response;
        }

        return null;
    }

    /**
     * Check if the responses are empty.
     *
     * @return bool
     */
    public function isEmpty(): bool
    {
        return empty($this->sequenceResponses) && empty($this->connectorResponses) && empty($this->requestResponses) && empty($this->urlResponses);
    }

    /**
     * Record a response.
     *
     * @param SaloonResponse $response
     * @return void
     */
    public function recordResponse(SaloonResponse $response): void
    {
        $this->recordedResponses[] = $response;
    }

    /**
     * Get all the recorded responses
     *
     * @return array
     */
    public function getRecordedResponses(): array
    {
        return $this->recordedResponses;
    }

    /**
     * Get the last request that the mock manager sent.
     *
     * @return SaloonRequest|null
     */
    public function getLastRequest(): ?SaloonRequest
    {
        return $this->getLastResponse()?->getOriginalRequest();
    }

    /**
     * Get the last response that the mock manager sent.
     *
     * @return SaloonResponse|null
     */
    public function getLastResponse(): ?SaloonResponse
    {
        $lastResponse = end($this->recordedResponses);

        reset($this->recordedResponses);

        return $lastResponse;
    }

    /**
     * Assert that a given request was sent.
     *
     * @param string|callable $value
     * @return void
     * @throws \ReflectionException
     */
    public function assertSent(string|callable $value): void
    {
        $result = $this->checkRequestWasSent($value);

        PHPUnit::assertTrue($result, 'An expected request was not sent.');
    }

    /**
     * Assert that a given request was not sent.
     *
     * @param string|callable $request
     * @return void
     * @throws \ReflectionException
     */
    public function assertNotSent(string|callable $request): void
    {
        $result = $this->checkRequestWasNotSent($request);

        PHPUnit::assertTrue($result, 'An unexpected request was sent.');
    }

    /**
     * Assert JSON data was sent
     *
     * @param string $request
     * @param array $data
     * @return void
     * @throws \ReflectionException
     */
    public function assertSentJson(string $request, array $data): void
    {
        $this->assertSent($request);

        $response = $this->findResponseByRequest($request);

        PHPUnit::assertEquals($response->json(), $data, 'Expected request data was not sent.');
    }

    /**
     * Assert that nothing was sent.
     *
     * @return void
     */
    public function assertNothingSent(): void
    {
        PHPUnit::assertEmpty($this->getRecordedResponses(), 'Requests were sent.');
    }

    /**
     * Assert a request count has been met.
     *
     * @param int $count
     * @return void
     */
    public function assertSentCount(int $count): void
    {
        PHPUnit::assertCount($count, $this->getRecordedResponses());
    }

    /**
     * Check if a given request was sent
     *
     * @param string|callable $request
     * @return bool
     * @throws \ReflectionException
     */
    protected function checkRequestWasSent(string|callable $request): bool
    {
        $result = false;

        if (is_callable($request)) {
            $result = $request($this->getLastRequest(), $this->getLastResponse());
        }

        if (is_string($request)) {
            if (class_exists($request) && ReflectionHelper::isSubclassOf($request, SaloonRequest::class)) {
                $result = ! is_null($this->findResponseByRequest($request));
            } else {
                $result = ! is_null($this->findResponseWithRequestUrl($request));
            }
        }

        return $result;
    }

    /**
     * Check if a request has not been sent.
     *
     * @param string|callable $request
     * @return bool
     * @throws \ReflectionException
     */
    protected function checkRequestWasNotSent(string|callable $request): bool
    {
        return ! $this->checkRequestWasSent($request);
    }

    /**
     * Assert a given request was sent.
     *
     * @param string $request
     * @return SaloonResponse|null
     */
    protected function findResponseByRequest(string $request): ?SaloonResponse
    {
        foreach ($this->getRecordedResponses() as $recordedResponse) {
            if ($recordedResponse->getOriginalRequest() instanceof $request) {
                return $recordedResponse;
            }
        }

        return null;
    }

    /**
     * Find a request that matches a given url pattern
     *
     * @param string $url
     * @return SaloonResponse|null
     */
    protected function findResponseWithRequestUrl(string $url): ?SaloonResponse
    {
        foreach ($this->getRecordedResponses() as $recordedResponse) {
            $request = $recordedResponse->getOriginalRequest();

            if (URLHelper::matches($url, $request->getFullRequestUrl())) {
                return $recordedResponse;
            }
        }

        return null;
    }
}

<?php

namespace Sammyjo20\Saloon\Helpers;

use InvalidArgumentException;
use ReflectionClass;
use Sammyjo20\Saloon\Clients\MockClient;
use Sammyjo20\Saloon\Http\MockResponse;
use Sammyjo20\Saloon\Http\SaloonConnector;
use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Http\SaloonResponse;
use Throwable;

class FixtureRecorder
{
    /**
     * The base path where the fixtures will be stored.
     *
     * @var string
     */
    protected string $fixtureDirectory;

    /**
     * Should the fixture recorder record on failures?
     *
     * @var bool
     */
    protected bool $recordFailures;

    /**
     * Constructor
     *
     * @param string $fixtureDirectory
     * @param bool $recordFailures
     */
    public function __construct(string $fixtureDirectory, bool $recordFailures = false)
    {
        if (! is_dir($fixtureDirectory)) {
            throw new InvalidArgumentException('The provided fixture directory is not a valid directory.');
        }

        $this->fixtureDirectory = rtrim($fixtureDirectory, '/ ');
        $this->recordFailures = $recordFailures;
    }

    /**
     * Record and reply a response.
     *
     * @param SaloonRequest $request
     * @param SaloonConnector|null $connector
     * @param string|null $fixtureName
     * @return SaloonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \JsonException
     * @throws \ReflectionException
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonException
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonInvalidMockResponseCaptureMethodException
     */
    public function record(SaloonRequest $request, SaloonConnector $connector = null, string $fixtureName = null): SaloonResponse
    {
        $fixtureName ??= (new ReflectionClass($request))->getShortName();

        $fixturePath = $this->fixtureDirectory . DIRECTORY_SEPARATOR . $fixtureName . '.json';

        try {
            $fixture = file_get_contents($fixturePath);
        } catch (Throwable $ex) {
            $fixture = null;
        }

        // If we have found a fixture, we will attempt to decode it and convert it into a MockResponse.

        if (isset($fixture)) {
            $fixture = json_decode($fixture, true, 512, JSON_THROW_ON_ERROR);
            $mockResponse = unserialize($fixture['mockResponse'], ['allowed_classes' => [MockResponse::class]]);

            return $this->sendRequest($request, $connector, new MockClient([$mockResponse]));
        }

        // However if the fixture does not exist, we will register a response interceptor which will
        // store the fixture if the request is successful, ready for the next request.

        $request->addResponseInterceptor(function (SaloonRequest $request, SaloonResponse $response) use ($fixturePath) {
            if ($this->recordFailures === false && $response->failed()) {
                return $response;
            }

            $mockResponse = new MockResponse($response->body(), $response->status(), $response->headers(true));
            $data = ['mockResponse' => serialize($mockResponse)];

            file_put_contents($fixturePath, json_encode($data, JSON_THROW_ON_ERROR));

            return $response;
        });

        return $this->sendRequest($request, $connector);
    }

    /**
     * Send the request.
     *
     * @param SaloonRequest $request
     * @param SaloonConnector|null $connector
     * @param MockClient|null $mockClient
     * @return SaloonResponse
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \ReflectionException
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonException
     */
    protected function sendRequest(SaloonRequest $request, SaloonConnector $connector = null, MockClient $mockClient = null): SaloonResponse
    {
        if ($connector instanceof SaloonConnector) {
            return $connector->send($request, $mockClient);
        }

        return $request->send($mockClient);
    }

    /**
     * Record failures.
     *
     * @return $this
     */
    public function recordFailures(): self
    {
        $this->recordFailures = true;

        return $this;
    }

    /**
     * Record failures.
     *
     * @return $this
     */
    public function doNotRecordFailures(): self
    {
        $this->recordFailures = false;

        return $this;
    }

    /**
     * Get the fixture directory.
     *
     * @return string
     */
    public function getFixtureDirectory(): string
    {
        return $this->fixtureDirectory;
    }
}

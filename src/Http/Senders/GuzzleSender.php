<?php

namespace Sammyjo20\Saloon\Http\Senders;

use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\BadResponseException;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Promise\FulfilledPromise;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\Utils;
use Psr\Http\Message\ResponseInterface;
use Sammyjo20\Saloon\Data\RequestDataType;
use Sammyjo20\Saloon\Exceptions\SaloonDuplicateHandlerException;
use Sammyjo20\Saloon\Exceptions\SaloonInvalidHandlerException;
use Sammyjo20\Saloon\Http\Guzzle\Middleware\MockMiddleware;
use Sammyjo20\Saloon\Http\MockResponse;
use Sammyjo20\Saloon\Http\PendingSaloonRequest;
use Sammyjo20\Saloon\Http\Responses\GuzzleResponse;
use Sammyjo20\Saloon\Http\Sender;
use Sammyjo20\Saloon\Http\Responses\SaloonResponse;

class GuzzleSender extends Sender
{
    /**
     * The Guzzle client.
     *
     * @var GuzzleClient
     */
    protected GuzzleClient $client;

    /**
     * Guzzle's Handler Stack.
     *
     * @var HandlerStack
     */
    protected HandlerStack $handlerStack;

    /**
     * Create the HTTP client.
     */
    public function __construct()
    {
        $this->client = $this->createGuzzleClient();
    }

    /**
     * Create a new Guzzle client
     *
     * @return GuzzleClient
     * @throws SaloonDuplicateHandlerException
     * @throws SaloonInvalidHandlerException
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonMissingMockException
     */
    private function createGuzzleClient(): GuzzleClient
    {
        $this->handlerStack = $this->createHandlerStack();

        $clientConfig = [
            'connect_timeout' => 10,
            'timeout' => 30,
            'http_errors' => true,
            'handler' => $this->handlerStack,
        ];

        return new GuzzleClient($clientConfig);
    }

    /**
     * Send a request
     *
     * @param PendingSaloonRequest $pendingRequest
     * @param bool $asynchronous
     * @return GuzzleResponse|PromiseInterface
     * @throws GuzzleException
     */
    public function sendRequest(PendingSaloonRequest $pendingRequest, bool $asynchronous = false): GuzzleResponse|PromiseInterface
    {
        return $asynchronous === true
            ? $this->sendAsynchronousRequest($pendingRequest)
            : $this->sendSynchronousRequest($pendingRequest);
    }

    /**
     * Send a synchronous request.
     *
     * @param PendingSaloonRequest $pendingRequest
     * @return SaloonResponse
     * @throws GuzzleException
     */
    protected function sendSynchronousRequest(PendingSaloonRequest $pendingRequest): SaloonResponse
    {
        $guzzleRequest = $this->createGuzzleRequest($pendingRequest);
        $guzzleRequestOptions = $this->createRequestOptions($pendingRequest);

        try {
            $guzzleResponse = $this->client->send($guzzleRequest, $guzzleRequestOptions);
        } catch (BadResponseException $exception) {
            return $this->createResponse($pendingRequest, $exception->getResponse(), $exception);
        }

        return $this->createResponse($pendingRequest, $guzzleResponse);
    }

    /**
     * Send an asynchronous request
     *
     * @param PendingSaloonRequest $pendingRequest
     * @return PromiseInterface
     */
    protected function sendAsynchronousRequest(PendingSaloonRequest $pendingRequest): PromiseInterface
    {
        $guzzleRequest = $this->createGuzzleRequest($pendingRequest);
        $guzzleRequestOptions = $this->createRequestOptions($pendingRequest);

        $promise = $this->client->sendAsync($guzzleRequest, $guzzleRequestOptions);

        return $this->processPromise($promise, $pendingRequest);
    }

    /**
     * Create the Guzzle request
     *
     * @param PendingSaloonRequest $request
     * @return Request
     */
    private function createGuzzleRequest(PendingSaloonRequest $request): Request
    {
        return new Request($request->getMethod()->value, $request->getUrl());
    }

    /**
     * Build up all the request options
     *
     * @param PendingSaloonRequest $request
     * @return array
     */
    private function createRequestOptions(PendingSaloonRequest $request): array
    {
        $requestOptions = [
            RequestOptions::HEADERS => $request->headers()->all(),
        ];

        foreach ($request->config()->all() as $configVariable => $value) {
            $requestOptions[$configVariable] = $value;
        }

        // Build up the data options

        $data = $request->data()->all();

        match ($request->getDataType()) {
            RequestDataType::JSON => $requestOptions['json'] = $data,
            RequestDataType::MULTIPART => $requestOptions['multipart'] = $data,
            RequestDataType::FORM => $requestOptions['form_params'] = $data,
            RequestDataType::MIXED => $requestOptions['body'] = $data,
            default => null,
        };

        return $requestOptions;
    }

    /**
     * Create a response.
     *
     * @param PendingSaloonRequest $pendingSaloonRequest
     * @param Response $guzzleResponse
     * @param RequestException|null $exception
     * @param bool $asPromise
     * @return SaloonResponse
     */
    private function createResponse(PendingSaloonRequest $pendingSaloonRequest, Response $guzzleResponse, RequestException $exception = null, bool $asPromise = false): SaloonResponse
    {
        $responseClass = $pendingSaloonRequest->getResponseClass();

        /** @var SaloonResponse $response */
        $response = new $responseClass($pendingSaloonRequest, $guzzleResponse, $exception);

        // Run the response pipeline

        return $this->handleResponse($response, $pendingSaloonRequest, $asPromise);
    }

    /**
     * Process the response.
     *
     * @param PendingSaloonRequest $pendingRequest
     * @param SaloonResponse $saloonResponse
     * @param bool $asPromise
     * @return GuzzleResponse|PromiseInterface
     */
    public function handleResponse(SaloonResponse $saloonResponse, PendingSaloonRequest $pendingRequest, bool $asPromise = false): GuzzleResponse|PromiseInterface
    {
        $saloonResponse = $pendingRequest->executeResponsePipeline($saloonResponse);

        // If we are mocking, we should record the request and response on the mock manager,
        // so we can run assertions on the responses.

        if ($pendingRequest->isMocking()) {
            $saloonResponse->setMocked(true);
            $pendingRequest->getMockClient()->recordResponse($saloonResponse);
        }

        return $saloonResponse;
    }

    /**
     * Get the base class that the custom responses should extend.
     *
     * @return string
     */
    public function getResponseClass(): string
    {
        return GuzzleResponse::class;
    }

    /**
     * Handle a mock response.
     *
     * @param MockResponse $mockResponse
     * @param PendingSaloonRequest $pendingRequest
     * @param bool $asPromise
     * @return GuzzleResponse|PromiseInterface
     * @throws GuzzleException
     */
    public function handleMockResponse(MockResponse $mockResponse, PendingSaloonRequest $pendingRequest, bool $asPromise = false): GuzzleResponse|PromiseInterface
    {
        // Todo: Make sure that this works even more concurrent requests.
        // Alternatively...

        // Always make sure the "MockMiddleware" is ALWAYS ready. Inside it, we check
        // if there is a mock response ready to be consumed - it there is, we consume
        // it, otherwise we continue. Might work better for concurrent requests.

        $this->pushMiddleware(new MockMiddleware($mockResponse), 'mock');

        $saloonResponse = $this->sendRequest($pendingRequest, $asPromise);

        $this->removeMiddleware('mock');

        return $saloonResponse;
    }

    /**
     * Update the promise provided by Guzzle.
     *
     * @param PromiseInterface $promise
     * @param PendingSaloonRequest $pendingRequest
     * @return PromiseInterface
     */
    private function processPromise(PromiseInterface $promise, PendingSaloonRequest $pendingRequest): PromiseInterface
    {
        return $promise
            ->then(
                function (ResponseInterface $guzzleResponse) use ($pendingRequest) {
                    // Instead of the promise returning a Guzzle response, we want to return
                    // a Saloon response.

                    return $this->createResponse($pendingRequest, $guzzleResponse);
                },
                function (GuzzleException $guzzleException) use ($pendingRequest) {
                    // If the exception was a connect exception, we should return that in the
                    // promise instead rather than trying to convert it into a
                    // SaloonResponse, since there was no response.

                    if (! $guzzleException instanceof RequestException) {
                        throw $guzzleException;
                    }

                    $response = $this->createResponse($pendingRequest, $guzzleException->getResponse(), $guzzleException);

                    throw $response->toException();
                }
            );
    }

    /**
     * Create a blank handler stack.
     *
     * @return HandlerStack
     */
    protected function createHandlerStack(): HandlerStack
    {
        $stack = new HandlerStack();
        $stack->setHandler(Utils::chooseHandler());

        return $stack;
    }

    /**
     * Add a middleware to the handler stack.
     *
     * @param callable $callable
     * @param string $withName
     * @return $this
     */
    public function pushMiddleware(callable $callable, string $withName = ''): self
    {
        $this->handlerStack->push($callable, $withName);

        return $this;
    }

    /**
     * Push a middleware before another.
     *
     * @param string $name
     * @param callable $callable
     * @param string $withName
     * @return $this
     */
    public function pushMiddlewareBefore(string $name, callable $callable, string $withName = ''): self
    {
        $this->handlerStack->before($name, $callable, $withName);

        return $this;
    }

    /**
     * Push a middleware after another.
     *
     * @param string $name
     * @param callable $callable
     * @param string $withName
     * @return $this
     */
    public function pushMiddlewareAfter(string $name, callable $callable, string $withName = ''): self
    {
        $this->handlerStack->after($name, $callable, $withName);

        return $this;
    }

    /**
     * Remove a middleware by name.
     *
     * @param string $name
     * @return $this
     */
    public function removeMiddleware(string $name): self
    {
        $this->handlerStack->remove($name);

        return $this;
    }

    /**
     * Overwrite the entire handler stack.
     *
     * @param HandlerStack $handlerStack
     * @return $this
     */
    public function setHandlerStack(HandlerStack $handlerStack): self
    {
        $this->handlerStack = $handlerStack;

        return $this;
    }

    /**
     * Get the handler stack.
     *
     * @return HandlerStack
     */
    public function getHandlerStack(): HandlerStack
    {
        return $this->handlerStack;
    }
}

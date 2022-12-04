<?php

declare(strict_types=1);

namespace Saloon\Http\Senders;

use Exception;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\TransferException;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\RequestOptions;
use Psr\Http\Message\ResponseInterface;
use Saloon\Contracts\PendingRequest;
use Saloon\Contracts\Response as ResponseContract;
use Saloon\Contracts\Sender;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Http\Responses\Response;
use Saloon\Repositories\Body\FormBodyRepository;
use Saloon\Repositories\Body\JsonBodyRepository;
use Saloon\Repositories\Body\MultipartBodyRepository;
use Saloon\Repositories\Body\StringBodyRepository;

class GuzzleSender implements Sender
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
     * Constructor
     *
     * Create the HTTP client.
     */
    public function __construct()
    {
        $this->client = $this->createGuzzleClient();
    }

    /**
     * Get the sender's response class
     *
     * @return string
     */
    public function getResponseClass(): string
    {
        return Response::class;
    }

    /**
     * Create a new Guzzle client
     *
     * @return GuzzleClient
     */
    protected function createGuzzleClient(): GuzzleClient
    {
        // We'll use HandlerStack::create as it will create a default
        // handler stack with the default Guzzle middleware like
        // http_errors, cookies etc.

        $this->handlerStack = HandlerStack::create();

        // Next we will define some Saloon defaults.

        $clientConfig = [
            'connect_timeout' => 10,
            'timeout' => 30,
            'http_errors' => true,
            'handler' => $this->handlerStack,
        ];

        // Something wrong with defining our own handler stack!

        return new GuzzleClient($clientConfig);
    }

    /**
     * Send a request
     *
     * @param \Saloon\Contracts\PendingRequest $pendingRequest
     * @param bool $asynchronous
     * @return \Saloon\Contracts\Response|\GuzzleHttp\Promise\PromiseInterface
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Saloon\Exceptions\Request\FatalRequestException
     */
    public function sendRequest(PendingRequest $pendingRequest, bool $asynchronous = false): ResponseContract|PromiseInterface
    {
        return $asynchronous === true
            ? $this->sendAsynchronousRequest($pendingRequest)
            : $this->sendSynchronousRequest($pendingRequest);
    }

    /**
     * Send a synchronous request.
     *
     * @param \Saloon\Contracts\PendingRequest $pendingRequest
     * @return \Saloon\Contracts\Response
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Saloon\Exceptions\Request\FatalRequestException
     */
    protected function sendSynchronousRequest(PendingRequest $pendingRequest): ResponseContract
    {
        $guzzleRequest = $this->createGuzzleRequest($pendingRequest);
        $guzzleRequestOptions = $this->createRequestOptions($pendingRequest);

        try {
            $guzzleResponse = $this->client->send($guzzleRequest, $guzzleRequestOptions);
        } catch (TransferException $exception) {
            // When the exception wasn't a RequestException, we'll throw a fatal
            // exception as this is likely a ConnectException, but it will
            // catch any new ones Guzzle release.

            if (! $exception instanceof RequestException) {
                throw new FatalRequestException($exception, $pendingRequest);
            }

            // Otherwise, we'll create a response.

            return $this->createResponse($pendingRequest, $exception->getResponse(), $exception);
        }

        return $this->createResponse($pendingRequest, $guzzleResponse);
    }

    /**
     * Send an asynchronous request
     *
     * @param PendingRequest $pendingRequest
     * @return PromiseInterface
     */
    protected function sendAsynchronousRequest(PendingRequest $pendingRequest): PromiseInterface
    {
        $guzzleRequest = $this->createGuzzleRequest($pendingRequest);
        $guzzleRequestOptions = $this->createRequestOptions($pendingRequest);

        $promise = $this->client->sendAsync($guzzleRequest, $guzzleRequestOptions);

        return $this->processPromise($promise, $pendingRequest);
    }

    /**
     * Create the Guzzle request
     *
     * @param PendingRequest $pendingRequest
     * @return Request
     */
    protected function createGuzzleRequest(PendingRequest $pendingRequest): Request
    {
        return new Request($pendingRequest->getMethod()->value, $pendingRequest->getUrl());
    }

    /**
     * Build up all the request options
     *
     * @param PendingRequest $pendingRequest
     * @return array
     */
    protected function createRequestOptions(PendingRequest $pendingRequest): array
    {
        $requestOptions = [];

        if ($pendingRequest->headers()->isNotEmpty()) {
            $requestOptions[RequestOptions::HEADERS] = $pendingRequest->headers()->all();
        }

        if ($pendingRequest->query()->isNotEmpty()) {
            $requestOptions[RequestOptions::QUERY] = $pendingRequest->query()->all();
        }

        foreach ($pendingRequest->config()->all() as $configVariable => $value) {
            $requestOptions[$configVariable] = $value;
        }

        $body = $pendingRequest->body();

        if (is_null($body) || $body->isEmpty()) {
            return $requestOptions;
        }

        match (true) {
            $body instanceof JsonBodyRepository => $requestOptions['json'] = $body->all(),
            $body instanceof MultipartBodyRepository => $requestOptions['multipart'] = $body->all(),
            $body instanceof FormBodyRepository => $requestOptions['form_params'] = $body->all(),
            $body instanceof StringBodyRepository => $requestOptions['body'] = $body->all(),
            default => $requestOptions['body'] = (string)$body,
        };

        return $requestOptions;
    }

    /**
     * Create a response.
     *
     * @param PendingRequest $pendingSaloonRequest
     * @param ResponseInterface $guzzleResponse
     * @param Exception|null $exception
     * @return ResponseContract
     */
    protected function createResponse(PendingRequest $pendingSaloonRequest, ResponseInterface $guzzleResponse, Exception $exception = null): ResponseContract
    {
        $responseClass = $pendingSaloonRequest->getResponseClass();

        return new $responseClass($pendingSaloonRequest, $guzzleResponse, $exception);
    }

    /**
     * Update the promise provided by Guzzle.
     *
     * @param PromiseInterface $promise
     * @param PendingRequest $pendingRequest
     * @return PromiseInterface
     */
    protected function processPromise(PromiseInterface $promise, PendingRequest $pendingRequest): PromiseInterface
    {
        return $promise
            ->then(
                function (ResponseInterface $guzzleResponse) use ($pendingRequest) {
                    // Instead of the promise returning a Guzzle response, we want to return
                    // a Saloon response.

                    return $this->createResponse($pendingRequest, $guzzleResponse);
                },
                function (TransferException $guzzleException) use ($pendingRequest) {
                    // When the exception wasn't a RequestException, we'll throw a fatal
                    // exception as this is likely a ConnectException, but it will
                    // catch any new ones Guzzle release.

                    if (! $guzzleException instanceof RequestException) {
                        throw new FatalRequestException($guzzleException, $pendingRequest);
                    }

                    // Otherwise we'll create a response to convert into an exception.
                    // This will run the exception through the exception handlers
                    // which allows the user to handle their own exceptions.

                    $response = $this->createResponse($pendingRequest, $guzzleException->getResponse(), $guzzleException);

                    // Throw the exception our way

                    throw $response->toException();
                }
            );
    }

    /**
     * Add a middleware to the handler stack.
     *
     * @param callable $callable
     * @param string $name
     * @return $this
     */
    public function addMiddleware(callable $callable, string $name = ''): static
    {
        $this->handlerStack->push($callable, $name);

        return $this;
    }

    /**
     * Overwrite the entire handler stack.
     *
     * @param HandlerStack $handlerStack
     * @return $this
     */
    public function setHandlerStack(HandlerStack $handlerStack): static
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

    /**
     * Get the Guzzle client
     *
     * @return GuzzleClient
     */
    public function getGuzzleClient(): GuzzleClient
    {
        return $this->client;
    }
}

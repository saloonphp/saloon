<?php

declare(strict_types=1);

namespace Saloon\Http\Senders;

use Exception;
use Saloon\Enums\Timeout;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use Saloon\Contracts\Sender;
use GuzzleHttp\RequestOptions;
use Saloon\Contracts\PendingRequest;
use GuzzleHttp\Client as GuzzleClient;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\TransferException;
use Saloon\Repositories\Body\FormBodyRepository;
use Saloon\Repositories\Body\JsonBodyRepository;
use Saloon\Contracts\Response as ResponseContract;
use Saloon\Repositories\Body\StreamBodyRepository;
use Saloon\Repositories\Body\StringBodyRepository;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Repositories\Body\MultipartBodyRepository;

class GuzzleSender implements Sender
{
    /**
     * The Guzzle client.
     *
     * @var \GuzzleHttp\Client
     */
    protected GuzzleClient $client;

    /**
     * Guzzle's Handler Stack.
     *
     * @var \GuzzleHttp\HandlerStack
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
     * Create a new Guzzle client
     *
     * @return \GuzzleHttp\Client
     */
    protected function createGuzzleClient(): GuzzleClient
    {
        // We'll use HandlerStack::create as it will create a default
        // handler stack with the default Guzzle middleware like
        // http_errors, cookies etc.

        $this->handlerStack = HandlerStack::create();

        // Now we'll return new Guzzle client with some default request
        // options configured. We'll also define the handler stack we
        // created above. Since it's a property, developers may
        // customise or add middleware to the handler stack.

        return new GuzzleClient([
            RequestOptions::CONNECT_TIMEOUT => Timeout::CONNECT->value,
            RequestOptions::TIMEOUT => Timeout::REQUEST->value,
            RequestOptions::HTTP_ERRORS => true,
            'handler' => $this->handlerStack,
        ]);
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

            return $this->createResponse($pendingRequest, $guzzleResponse);
        } catch (ConnectException $exception) {
            // ConnectException means a network exception has happened, like Guzzle
            // not being able to connect to the host.

            throw new FatalRequestException($exception, $pendingRequest);
        } catch (RequestException $exception) {
            // Sometimes, Guzzle will throw a RequestException without a response. This
            // means that it was fatal, so we should still throw a fatal request exception.

            if (is_null($exception->getResponse())) {
                throw new FatalRequestException($exception, $pendingRequest);
            }

            return $this->createResponse($pendingRequest, $exception->getResponse(), $exception);
        }
    }

    /**
     * Send an asynchronous request
     *
     * @param \Saloon\Contracts\PendingRequest $pendingRequest
     * @return \GuzzleHttp\Promise\PromiseInterface
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
     * @param \Saloon\Contracts\PendingRequest $pendingRequest
     * @return \GuzzleHttp\Psr7\Request
     */
    protected function createGuzzleRequest(PendingRequest $pendingRequest): Request
    {
        return new Request($pendingRequest->getMethod()->value, $pendingRequest->getUrl());
    }

    /**
     * Build up all the request options
     *
     * @param \Saloon\Contracts\PendingRequest $pendingRequest
     * @return array<RequestOptions::*, mixed>
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

        if ($pendingRequest->delay()->isNotEmpty()) {
            $requestOptions[RequestOptions::DELAY] = $pendingRequest->delay()->get();
        }

        $body = $pendingRequest->body();

        if (is_null($body) || $body->isEmpty()) {
            return $requestOptions;
        }

        match (true) {
            $body instanceof JsonBodyRepository => $requestOptions[RequestOptions::JSON] = $body->all(),
            $body instanceof MultipartBodyRepository => $requestOptions[RequestOptions::MULTIPART] = $body->toArray(),
            $body instanceof FormBodyRepository => $requestOptions[RequestOptions::FORM_PARAMS] = $body->all(),
            $body instanceof StringBodyRepository => $requestOptions[RequestOptions::BODY] = $body->all(),
            $body instanceof StreamBodyRepository => $requestOptions[RequestOptions::BODY] = $body->all(),
            default => $requestOptions[RequestOptions::BODY] = (string)$body,
        };

        return $requestOptions;
    }

    /**
     * Create a response.
     *
     * @param \Saloon\Contracts\PendingRequest $pendingSaloonRequest
     * @param \Psr\Http\Message\ResponseInterface $guzzleResponse
     * @param \Exception|null $exception
     * @return \Saloon\Contracts\Response
     */
    protected function createResponse(PendingRequest $pendingSaloonRequest, ResponseInterface $guzzleResponse, Exception $exception = null): ResponseContract
    {
        /** @var class-string<\Saloon\Contracts\Response> $responseClass */
        $responseClass = $pendingSaloonRequest->getResponseClass();

        return $responseClass::fromPsrResponse($guzzleResponse, $pendingSaloonRequest, $exception);
    }

    /**
     * Update the promise provided by Guzzle.
     *
     * @param \GuzzleHttp\Promise\PromiseInterface $promise
     * @param \Saloon\Contracts\PendingRequest $pendingRequest
     * @return \GuzzleHttp\Promise\PromiseInterface
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

                    // Sometimes, Guzzle will throw a RequestException without a response. This
                    // means that it was fatal, so we should still throw a fatal request exception.

                    if (is_null($guzzleException->getResponse())) {
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
     * @param \GuzzleHttp\HandlerStack $handlerStack
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
     * @return \GuzzleHttp\HandlerStack
     */
    public function getHandlerStack(): HandlerStack
    {
        return $this->handlerStack;
    }

    /**
     * Get the Guzzle client
     *
     * @return \GuzzleHttp\Client
     */
    public function getGuzzleClient(): GuzzleClient
    {
        return $this->client;
    }
}

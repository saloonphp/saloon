<?php

namespace Sammyjo20\Saloon\Http\Senders;

use GuzzleHttp\Utils;
use GuzzleHttp\HandlerStack;
use GuzzleHttp\Psr7\Request;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\RequestOptions;
use GuzzleHttp\Client as GuzzleClient;
use Sammyjo20\Saloon\Contracts\Sender;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Exception\GuzzleException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\BadResponseException;
use Sammyjo20\Saloon\Http\PendingSaloonRequest;
use Sammyjo20\Saloon\Http\Responses\PsrResponse;
use Sammyjo20\Saloon\Repositories\Body\FormBodyRepository;
use Sammyjo20\Saloon\Repositories\Body\JsonBodyRepository;
use Sammyjo20\Saloon\Repositories\Body\StringBodyRepository;
use Sammyjo20\Saloon\Repositories\Body\MultipartBodyRepository;

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
     * Create a new Guzzle client
     *
     * @return GuzzleClient
     */
    protected function createGuzzleClient(): GuzzleClient
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
     * @return PsrResponse|PromiseInterface
     * @throws GuzzleException
     */
    public function sendRequest(PendingSaloonRequest $pendingRequest, bool $asynchronous = false): PsrResponse|PromiseInterface
    {
        return $asynchronous === true
            ? $this->sendAsynchronousRequest($pendingRequest)
            : $this->sendSynchronousRequest($pendingRequest);
    }

    /**
     * Send a synchronous request.
     *
     * @param PendingSaloonRequest $pendingRequest
     * @return PsrResponse
     * @throws GuzzleException
     */
    protected function sendSynchronousRequest(PendingSaloonRequest $pendingRequest): PsrResponse
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

        $body = $request->body();

        if (is_null($body) || $body->isEmpty()) {
            return $requestOptions;
        }

        match ($body::class) {
            JsonBodyRepository::class => $requestOptions['json'] = $body->all(),
            MultipartBodyRepository::class => $requestOptions['multipart'] = $body->all(),
            FormBodyRepository::class => $requestOptions['form_params'] = $body->all(),
            StringBodyRepository::class => $requestOptions['body'] = $body->all(),
            default => $requestOptions['body'] = (string)$body,
        };

        return $requestOptions;
    }

    /**
     * Create a response.
     *
     * @param PendingSaloonRequest $pendingSaloonRequest
     * @param Response $guzzleResponse
     * @param RequestException|null $exception
     * @return PsrResponse
     */
    private function createResponse(PendingSaloonRequest $pendingSaloonRequest, Response $guzzleResponse, RequestException $exception = null): PsrResponse
    {
        $responseClass = $pendingSaloonRequest->getResponseClass();

        return new $responseClass($pendingSaloonRequest, $guzzleResponse, $exception);
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
     * Add a middleware to the handler stack.
     *
     * @param callable $callable
     * @param string $withName
     * @return $this
     */
    public function pushMiddleware(callable $callable, string $withName = ''): static
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
    public function pushMiddlewareBefore(string $name, callable $callable, string $withName = ''): static
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
    public function pushMiddlewareAfter(string $name, callable $callable, string $withName = ''): static
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
    public function removeMiddleware(string $name): static
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
    public function setHandlerStack(HandlerStack $handlerStack): static
    {
        $this->handlerStack = $handlerStack;

        return $this;
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
     * Get the handler stack.
     *
     * @return HandlerStack
     */
    public function getHandlerStack(): HandlerStack
    {
        return $this->handlerStack;
    }

    /**
     * Get the sender's response class
     *
     * @return string
     */
    public function getResponseClass(): string
    {
        return PsrResponse::class;
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

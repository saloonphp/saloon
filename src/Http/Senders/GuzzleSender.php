<?php

declare(strict_types=1);

namespace Saloon\Http\Senders;

use Exception;
use Saloon\Config;
use Saloon\Http\Response;
use GuzzleHttp\HandlerStack;
use Saloon\Contracts\Sender;
use GuzzleHttp\RequestOptions;
use Saloon\Http\PendingRequest;
use GuzzleHttp\Psr7\HttpFactory;
use Saloon\Data\FactoryCollection;
use GuzzleHttp\Client as GuzzleClient;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use GuzzleHttp\Promise\PromiseInterface;
use GuzzleHttp\Exception\ConnectException;
use GuzzleHttp\Exception\RequestException;
use GuzzleHttp\Exception\ResponseTransferException;
use GuzzleHttp\Exception\TransferException;
use Saloon\Exceptions\Request\FatalRequestException;
use Saloon\Http\Senders\Factories\GuzzleMultipartBodyFactory;

class GuzzleSender implements Sender
{
    /**
     * The Guzzle client.
     */
    protected GuzzleClient $client;

    /**
     * Guzzle's Handler Stack.
     *
     * @var HandlerStack<callable(RequestInterface, array<array-key, mixed>): PromiseInterface<ResponseInterface, mixed>>
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
     * Get the factory collection
     */
    public function getFactoryCollection(): FactoryCollection
    {
        $factory = new HttpFactory;

        return new FactoryCollection(
            requestFactory: $factory,
            uriFactory: $factory,
            streamFactory: $factory,
            responseFactory: $factory,
            multipartBodyFactory: new GuzzleMultipartBodyFactory,
        );
    }

    /**
     * Create a new Guzzle client
     */
    protected function createGuzzleClient(): GuzzleClient
    {
        $this->handlerStack = $this->defaultHandlerStack();

        // Now we'll return new Guzzle client with some default request
        // options configured. We'll also define the handler stack we
        // created above. Since it's a property, developers may
        // customise or add middleware to the handler stack.

        return new GuzzleClient([
            RequestOptions::CRYPTO_METHOD => Config::$defaultTlsMethod,
            RequestOptions::CONNECT_TIMEOUT => Config::$defaultConnectionTimeout,
            RequestOptions::TIMEOUT => Config::$defaultRequestTimeout,
            RequestOptions::HTTP_ERRORS => true,
            'handler' => $this->handlerStack,
        ]);
    }

    /**
     * The default handler stack used by the underlying Guzzle client
     *
     * @return HandlerStack<callable(RequestInterface, array<array-key, mixed>): PromiseInterface<ResponseInterface, mixed>>
     */
    protected function defaultHandlerStack(): HandlerStack
    {
        // We'll use HandlerStack::create as it will create a default
        // handler stack with the default Guzzle middleware like
        // http_errors, cookies etc.

        return HandlerStack::create();
    }

    /**
     * Send a synchronous request.
     *
     * @throws \GuzzleHttp\Exception\GuzzleException
     * @throws \Saloon\Exceptions\Request\FatalRequestException
     */
    public function send(PendingRequest $pendingRequest): Response
    {
        $request = $pendingRequest->createPsrRequest();
        $requestOptions = $pendingRequest->config()->all();

        try {
            $guzzleResponse = $this->client->send($request, $requestOptions);

            return $this->createResponse($guzzleResponse, $pendingRequest, $request);
        } catch (ConnectException $exception) {
            throw new FatalRequestException($exception, $pendingRequest);
        } catch (RequestException $exception) {
            return $this->responseFromRequestException($exception, $pendingRequest, $request);
        } catch (TransferException $exception) {
            // Guzzle 8: timeouts and network errors before a response (NetworkException, etc.)
            // are no longer ConnectException. Matches processPromise() behaviour.

            throw new FatalRequestException($exception, $pendingRequest);
        }
    }

    /**
     * Send an asynchronous request
     */
    public function sendAsync(PendingRequest $pendingRequest): PromiseInterface
    {
        $request = $pendingRequest->createPsrRequest();
        $requestOptions = $pendingRequest->config()->all();

        $promise = $this->client->sendAsync($request, $requestOptions);

        return $this->processPromise($request, $promise, $pendingRequest);
    }

    /**
     * Update the promise provided by Guzzle.
     */
    protected function processPromise(RequestInterface $psrRequest, PromiseInterface $promise, PendingRequest $pendingRequest): PromiseInterface
    {
        return $promise
            ->then(
                function (ResponseInterface $guzzleResponse) use ($psrRequest, $pendingRequest) {
                    // Instead of the promise returning a Guzzle response, we want to return
                    // a Saloon response.

                    return $this->createResponse($guzzleResponse, $pendingRequest, $psrRequest);
                },
                function (TransferException $guzzleException) use ($pendingRequest, $psrRequest) {
                    // When the exception wasn't a RequestException, we'll throw a fatal
                    // exception as this is likely a ConnectException, but it will
                    // catch any new ones Guzzle release.

                    if (! $guzzleException instanceof RequestException) {
                        throw new FatalRequestException($guzzleException, $pendingRequest);
                    }

                    $response = $this->responseFromRequestException($guzzleException, $pendingRequest, $psrRequest);

                    return ($exception = $response->toException()) ? throw $exception : $response;
                }
            );
    }

    /**
     * Build a Saloon response from a Guzzle RequestException, or throw when the transfer was fatal.
     *
     * @throws FatalRequestException
     */
    protected function responseFromRequestException(
        RequestException $exception,
        PendingRequest $pendingRequest,
        RequestInterface $psrRequest,
    ): Response {
        if ($this->isIncompleteResponseTransfer($exception)) {
            throw new FatalRequestException($exception, $pendingRequest);
        }

        $guzzleResponse = $exception->getResponse();

        if ($guzzleResponse === null) {
            throw new FatalRequestException($exception, $pendingRequest);
        }

        return $this->createResponse($guzzleResponse, $pendingRequest, $psrRequest, $exception);
    }

    /**
     * Guzzle 8 may return a partial response when the body transfer fails (ResponseTransferException).
     */
    protected function isIncompleteResponseTransfer(RequestException $exception): bool
    {
        return class_exists(ResponseTransferException::class)
            && $exception instanceof ResponseTransferException;
    }

    /**
     * Create a response.
     */
    protected function createResponse(ResponseInterface $psrResponse, PendingRequest $pendingRequest, RequestInterface $psrRequest, ?Exception $exception = null): Response
    {
        /** @var class-string<\Saloon\Http\Response> $responseClass */
        $responseClass = $pendingRequest->getResponseClass();

        return $responseClass::fromPsrResponse($psrResponse, $pendingRequest, $psrRequest, $exception);
    }

    /**
     * Add a middleware to the handler stack.
     *
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
     * @param HandlerStack<callable(RequestInterface, array<array-key, mixed>): PromiseInterface<ResponseInterface, mixed>> $handlerStack
     *
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
     * @return HandlerStack<callable(RequestInterface, array<array-key, mixed>): PromiseInterface<ResponseInterface, mixed>>
     */
    public function getHandlerStack(): HandlerStack
    {
        return $this->handlerStack;
    }

    /**
     * Get the Guzzle client
     */
    public function getGuzzleClient(): GuzzleClient
    {
        return $this->client;
    }
}

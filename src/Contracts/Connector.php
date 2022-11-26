<?php

declare(strict_types=1);

namespace Saloon\Contracts;

use GuzzleHttp\Promise\PromiseInterface;

interface Connector
{
    /**
     * Retrieve the authenticator.
     *
     * @return Authenticator|null
     */
    public function getAuthenticator(): ?Authenticator;

    /**
     * Authenticate the request with an authenticator.
     *
     * @param Authenticator $authenticator
     * @return \Saloon\Http\Connector
     */
    public function authenticateWith(Authenticator $authenticator): static;

    /**
     * Authenticate the request with an Authorization header.
     *
     * @param string $token
     * @param string $prefix
     * @return \Saloon\Http\Connector
     */
    public function withTokenAuth(string $token, string $prefix = 'Bearer'): static;

    /**
     * Authenticate the request with "basic" authentication.
     *
     * @param string $username
     * @param string $password
     * @return \Saloon\Http\Connector
     */
    public function withBasicAuth(string $username, string $password): static;

    /**
     * Authenticate the request with "digest" authentication.
     *
     * @param string $username
     * @param string $password
     * @param string $digest
     * @return \Saloon\Http\Connector
     */
    public function withDigestAuth(string $username, string $password, string $digest): static;

    /**
     * Authenticate the request with a query parameter token.
     *
     * @param string $parameter
     * @param string $value
     * @return \Saloon\Http\Connector
     */
    public function withQueryAuth(string $parameter, string $value): static;

    /**
     * Handle the boot lifecycle hook
     *
     * @param PendingRequest $pendingRequest
     * @return void
     */
    public function boot(PendingRequest $pendingRequest): void;

    /**
     * Cast the response to a DTO.
     *
     * @param Response $response
     * @return mixed
     */
    public function createDtoFromResponse(Response $response): mixed;

    /**
     * Invoke a callable where a given value returns a truthy value.
     *
     * @param mixed $value
     * @param callable $callback
     * @param callable|null $default
     * @return \Saloon\Http\Connector
     */
    public function when(mixed $value, callable $callback, callable $default = null): static;

    /**
     * Invoke a callable when a given value returns a falsy value.
     *
     * @param mixed $value
     * @param callable $callback
     * @param mixed $default
     * @return \Saloon\Http\Connector
     */
    public function unless(mixed $value, callable $callback, callable $default = null): static;

    /**
     * Define the base URL of the API.
     *
     * @return string
     */
    public function defineBaseUrl(): string;

    /**
     * Prepare a new request by providing it the current instance of the connector.
     *
     * @param Request $request
     * @return Request
     */
    public function request(Request $request): Request;

    /**
     * Instantiate a new class with the arguments.
     *
     * @param mixed ...$arguments
     * @return \Saloon\Http\Connector
     */
    public static function make(...$arguments): static;

    /**
     * Access the config
     *
     * @return ArrayStore
     */
    public function config(): ArrayStore;

    /**
     * Get the response class
     *
     * @return string
     */
    public function getResponseClass(): string;

    /**
     * Access the headers
     *
     * @return ArrayStore
     */
    public function headers(): ArrayStore;

    /**
     * Access the middleware pipeline
     *
     * @return MiddlewarePipeline
     */
    public function middleware(): MiddlewarePipeline;

    /**
     * Specify a mock client.
     *
     * @param MockClient $mockClient
     * @return \Saloon\Http\Connector
     */
    public function withMockClient(MockClient $mockClient): static;

    /**
     * Get the mock client.
     *
     * @return MockClient|null
     */
    public function getMockClient(): ?MockClient;

    /**
     * Determine if the instance has a mock client
     *
     * @return bool
     */
    public function hasMockClient(): bool;

    /**
     * Create a request pool
     *
     * @param iterable|callable $requests
     * @param int|callable $concurrency
     * @param callable|null $responseHandler
     * @param callable|null $exceptionHandler
     * @return Pool
     */
    public function pool(iterable|callable $requests = [], int|callable $concurrency = 5, callable|null $responseHandler = null, callable|null $exceptionHandler = null): Pool;

    /**
     * Access the query parameters
     *
     * @return ArrayStore
     */
    public function query(): ArrayStore;

    /**
     * Manage the request sender.
     *
     * @return Sender
     */
    public function sender(): Sender;

    /**
     * Bootstrap and get the registered requests in the $requests array.
     *
     * @return array
     */
    public function getRegisteredRequests(): array;

    /**
     * Send a request
     *
     * @param Request $request
     * @param MockClient|null $mockClient
     * @return Response
     */
    public function send(Request $request, MockClient $mockClient = null): Response;

    /**
     * Send a request asynchronously
     *
     * @param Request $request
     * @param MockClient|null $mockClient
     * @return PromiseInterface
     */
    public function sendAsync(Request $request, MockClient $mockClient = null): PromiseInterface;
}

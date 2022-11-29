<?php

declare(strict_types=1);

namespace Saloon\Contracts;

use GuzzleHttp\Promise\PromiseInterface;

interface Request
{
    /**
     * Retrieve the authenticator.
     *
     * @return \Saloon\Contracts\Authenticator|null
     */
    public function getAuthenticator(): ?Authenticator;

    /**
     * Authenticate the request with an authenticator.
     *
     * @param \Saloon\Contracts\Authenticator $authenticator
     * @return $this
     */
    public function authenticate(Authenticator $authenticator): static;

    /**
     * Authenticate the request with an Authorization header.
     *
     * @param string $token
     * @param string $prefix
     * @return $this
     */
    public function withTokenAuth(string $token, string $prefix = 'Bearer'): static;

    /**
     * Authenticate the request with "basic" authentication.
     *
     * @param string $username
     * @param string $password
     * @return $this
     */
    public function withBasicAuth(string $username, string $password): static;

    /**
     * Authenticate the request with "digest" authentication.
     *
     * @param string $username
     * @param string $password
     * @param string $digest
     * @return $this
     */
    public function withDigestAuth(string $username, string $password, string $digest): static;

    /**
     * Authenticate the request with a query parameter token.
     *
     * @param string $parameter
     * @param string $value
     * @return $this
     */
    public function withQueryAuth(string $parameter, string $value): static;

    /**
     * Handle the boot lifecycle hook
     *
     * @param \Saloon\Contracts\PendingRequest $pendingRequest
     * @return void
     */
    public function boot(PendingRequest $pendingRequest): void;

    /**
     * Build up the full request URL.
     *
     * @return string
     */
    public function getRequestUrl(): string;

    /**
     * Cast the response to a DTO.
     *
     * @param \Saloon\Contracts\Response $response
     * @return mixed
     */
    public function createDtoFromResponse(Response $response): mixed;

    /**
     * Invoke a callable where a given value returns a truthy value.
     *
     * @param mixed $value
     * @param callable $callback
     * @param callable|null $default
     * @return $this
     */
    public function when(mixed $value, callable $callback, callable $default = null): static;

    /**
     * Invoke a callable when a given value returns a falsy value.
     *
     * @param mixed $value
     * @param callable $callback
     * @param mixed $default
     * @return $this
     */
    public function unless(mixed $value, callable $callback, callable $default = null): static;

    /**
     * Access the config
     *
     * @return \Saloon\Contracts\ArrayStore
     */
    public function config(): ArrayStore;

    /**
     * Retrieve the loaded connector.
     *
     * @return \Saloon\Contracts\Connector
     */
    public function connector(): Connector;

    /**
     * Set the loaded connector at runtime.
     *
     * @param \Saloon\Contracts\Connector $connector
     * @return $this
     */
    public function setConnector(Connector $connector): static;

    /**
     * Get the response class
     *
     * @return string
     */
    public function getResponseClass(): string;

    /**
     * Access the headers
     *
     * @return \Saloon\Contracts\ArrayStore
     */
    public function headers(): ArrayStore;

    /**
     * Access the middleware pipeline
     *
     * @return \Saloon\Contracts\MiddlewarePipeline
     */
    public function middleware(): MiddlewarePipeline;

    /**
     * Specify a mock client.
     *
     * @param \Saloon\Contracts\MockClient $mockClient
     * @return $this
     */
    public function withMockClient(MockClient $mockClient): static;

    /**
     * Get the mock client.
     *
     * @return \Saloon\Contracts\MockClient|null
     */
    public function getMockClient(): ?MockClient;

    /**
     * Determine if the instance has a mock client
     *
     * @return bool
     */
    public function hasMockClient(): bool;

    /**
     * Create a pending request
     *
     * @param \Saloon\Contracts\MockClient|null $mockClient
     * @return \Saloon\Contracts\PendingRequest<static>
     */
    public function createPendingRequest(MockClient $mockClient = null): PendingRequest;

    /**
     * Access the HTTP sender
     *
     * @return \Saloon\Contracts\Sender
     */
    public function sender(): Sender;

    /**
     * Send a request synchronously
     *
     * @param \Saloon\Contracts\MockClient|null $mockClient
     * @return \Saloon\Contracts\Response
     */
    public function send(MockClient $mockClient = null): Response;

    /**
     * Send a request asynchronously
     *
     * @param \Saloon\Contracts\MockClient|null $mockClient
     * @return \GuzzleHttp\Promise\PromiseInterface
     */
    public function sendAsync(MockClient $mockClient = null): PromiseInterface;

    /**
     * Instantiate a new class with the arguments.
     *
     * @param ...$arguments
     * @return static
     */
    public static function make(...$arguments): static;

    /**
     * Get the method of the request.
     *
     * @return string
     */
    public function getMethod(): string;
}

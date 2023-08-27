<?php

declare(strict_types=1);

namespace Saloon\Contracts;

use Throwable;
use SimpleXMLElement;
use Illuminate\Support\Collection;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;

/**
 * @internal
 */
interface Response extends HasHeaders
{
    /**
     * Create an instance of the response from a PSR response
     *
     * @param \Psr\Http\Message\ResponseInterface $psrResponse
     * @param \Saloon\Contracts\PendingRequest $pendingRequest
     * @param \Psr\Http\Message\RequestInterface $psrRequest
     * @param \Throwable|null $senderException
     * @return $this
     */
    public static function fromPsrResponse(ResponseInterface $psrResponse, PendingRequest $pendingRequest, RequestInterface $psrRequest, ?Throwable $senderException = null): static;

    /**
     * Get the PSR request instance
     */
    public function getPsrRequest(): RequestInterface;

    /**
     * Create a PSR response from the raw response.
     */
    public function getPsrResponse(): ResponseInterface;

    /**
     * Get the underlying PendingRequest that created the response
     */
    public function getPendingRequest(): PendingRequest;

    /**
     * Get the connector that sent the request
     */
    public function getConnector(): Connector;

    /**
     * Get the original request
     *
     * @deprecated Will be removed in Saloon v4. Use $response->getPendingRequest()->getRequest() instead.
     */
    public function getRequest(): Request;

    /**
     * Get the body of the response as string.
     */
    public function body(): string;

    /**
     * Get the body as a stream. Don't forget to close the stream after using ->close().
     */
    public function stream(): StreamInterface;

    /**
     * Create a temporary resource for the stream.
     *
     * Useful for storing the file.
     *
     * @return mixed
     */
    public function getRawStream(): mixed;

    /**
     * Save the body to a file
     *
     * @param string|resource $resourceOrPath
     * @param bool $closeResource
     * @return void
     */
    public function saveBodyToFile(mixed $resourceOrPath, bool $closeResource = true): void;

    /**
     * Close the stream and any underlying resources.
     *
     * @return $this
     */
    public function close(): static;

    /**
     * Get a header from the response.
     *
     * @return string|array<array-key, mixed>|null
     */
    public function header(string $header): string|array|null;

    /**
     * Get the status code of the response.
     */
    public function status(): int;

    /**
     * Get the JSON decoded body of the response as an array or scalar value.
     *
     * @param array-key|null $key
     * @return ($key is null ? array<array-key, mixed> : mixed)
     */
    public function json(string|int|null $key = null, mixed $default = null): mixed;

    /**
     * Get the JSON decoded body as an array. Provide a key to find a specific item in the JSON.
     *
     * Alias of json()
     *
     * @param array-key|null $key
     * @return ($key is null ? array<array-key, mixed> : mixed)
     */
    public function array(string|int|null $key = null, mixed $default = null): mixed;

    /**
     * Get the JSON decoded body of the response as an object.
     */
    public function object(): object;

    /**
     * Convert the XML response into a SimpleXMLElement.
     */
    public function xml(mixed ...$arguments): SimpleXMLElement|bool;

    /**
     * Get the JSON decoded body of the response as a collection.
     *
     * @param array-key|null $key
     * @return \Illuminate\Support\Collection<array-key, mixed>
     */
    public function collect(string|int|null $key = null): Collection;

    /**
     * Convert the response into a DTO
     */
    public function dto(): mixed;

    /**
     * Convert the response into a DTO or throw a LogicException if the response failed
     *
     * @throws \LogicException
     */
    public function dtoOrFail(): mixed;

    /**
     * Determine if the request was successful.
     */
    public function successful(): bool;

    /**
     * Determine if the response code was "OK".
     */
    public function ok(): bool;

    /**
     * Determine if the response was a redirect.
     */
    public function redirect(): bool;

    /**
     * Determine if the response indicates a client or server error occurred.
     */
    public function failed(): bool;

    /**
     * Determine if the response should throw a request exception
     */
    public function shouldThrowRequestException(): bool;

    /**
     * Determine if the response indicates a client error occurred.
     */
    public function clientError(): bool;

    /**
     * Determine if the response indicates a server error occurred.
     */
    public function serverError(): bool;

    /**
     * Execute the given callback if there was a server or client error.
     *
     * @param callable($this): (void) $callback
     * @return $this
     */
    public function onError(callable $callback): static;

    /**
     * Create an exception if a server or client error occurred.
     */
    public function toException(): ?Throwable;

    /**
     * Throw an exception if a server or client error occurred.
     *
     * @return $this
     */
    public function throw(): static;

    /**
     * Check if the response has been cached
     */
    public function isCached(): bool;

    /**
     * Check if the response has been mocked
     */
    public function isMocked(): bool;

    /**
     * Check if a response has been faked
     */
    public function isFaked(): bool;

    /**
     * Set if a response has been mocked or not.
     *
     * @return $this
     */
    public function setMocked(bool $value): static;

    /**
     * Set if a response has been cached or not.
     *
     * @return $this
     */
    public function setCached(bool $value): static;

    /**
     * Set the simulated response payload if the response was simulated.
     *
     * @param \Saloon\Contracts\FakeResponse $fakeResponse
     * @return $this
     */
    public function setFakeResponse(FakeResponse $fakeResponse): static;

    /**
     * Get the simulated response payload if the response was simulated.
     *
     * @return \Saloon\Contracts\FakeResponse|null
     */
    public function getFakeResponse(): ?FakeResponse;

    /**
     * Get the original sender exception
     */
    public function getSenderException(): ?Throwable;

    /**
     * Get the body of the response as a string
     */
    public function __toString(): string;
}

<?php

declare(strict_types=1);

namespace Saloon\Contracts;

use Throwable;
use SimpleXMLElement;
use Illuminate\Support\Collection;
use Saloon\Repositories\ArrayStore;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\ResponseInterface;

interface Response
{
    /**
     * Create an instance of the response
     *
     * @param \Saloon\Contracts\PendingRequest $pendingRequest
     * @param \Psr\Http\Message\ResponseInterface $psrResponse
     * @param \Throwable|null $senderException
     * @return $this
     */
    public static function fromPsrResponse(ResponseInterface $psrResponse, PendingRequest $pendingRequest, Throwable $senderException = null): static;

    /**
     * Get the body of the response as string.
     *
     * @return string
     */
    public function body(): string;

    /**
     * Get the body as a stream. Don't forget to close the stream after using ->close().
     *
     * @return StreamInterface
     */
    public function stream(): StreamInterface;

    /**
     * Get the headers from the response.
     *
     * @return ArrayStore
     */
    public function headers(): ArrayStore;

    /**
     * Get the status code of the response.
     *
     * @return int
     */
    public function status(): int;

    /**
     * Create a PSR response from the raw response.
     *
     * @return ResponseInterface
     */
    public function getPsrResponse(): ResponseInterface;

    /**
     * @return PendingRequest
     */
    public function getPendingRequest(): PendingRequest;

    /**
     * Get the original request
     *
     * @return Request
     */
    public function getRequest(): Request;

    /**
     * Get the JSON decoded body of the response as an array or scalar value.
     *
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    public function json(string $key = null, mixed $default = null): mixed;

    /**
     * Get the JSON decoded body of the response as an object.
     *
     * @return object
     */
    public function object(): object;

    /**
     * Convert the XML response into a SimpleXMLElement.
     *
     * @param ...$arguments
     * @return SimpleXMLElement|bool
     */
    public function xml(...$arguments): SimpleXMLElement|bool;

    /**
     * Get the JSON decoded body of the response as a collection.
     *
     * @param $key
     * @return Collection
     */
    public function collect($key = null): Collection;

    /**
     * Cast the response to a DTO.
     *
     * @return mixed
     */
    public function dto(): mixed;

    /**
     * Determine if the request was successful.
     *
     * @return bool
     */
    public function successful(): bool;

    /**
     * Determine if the response code was "OK".
     *
     * @return bool
     */
    public function ok(): bool;

    /**
     * Determine if the response was a redirect.
     *
     * @return bool
     */
    public function redirect(): bool;

    /**
     * Determine if the response indicates a client or server error occurred.
     *
     * @return bool
     */
    public function failed(): bool;

    /**
     * Determine if the response should throw a request exception
     *
     * @return bool
     */
    public function shouldThrowRequestException(): bool;

    /**
     * Determine if the response indicates a client error occurred.
     *
     * @return bool
     */
    public function clientError(): bool;

    /**
     * Determine if the response indicates a server error occurred.
     *
     * @return bool
     */
    public function serverError(): bool;

    /**
     * Execute the given callback if there was a server or client error.
     *
     * @param callable $callback
     * @return $this
     */
    public function onError(callable $callback): static;

    /**
     * Create an exception if a server or client error occurred.
     *
     * @return Throwable|null
     */
    public function toException(): ?Throwable;

    /**
     * Throw an exception if a server or client error occurred.
     *
     * @return $this
     */
    public function throw(): static;

    /**
     * Get the body of the response.
     *
     * @return string
     */
    public function __toString(): string;

    /**
     * Check if the response has been cached
     *
     * @return bool
     */
    public function isCached(): bool;

    /**
     * Check if the response has been mocked
     *
     * @return bool
     */
    public function isMocked(): bool;

    /**
     * Check if a response has been simulated
     *
     * @return bool
     */
    public function isSimulated(): bool;

    /**
     * Set if a response has been mocked or not.
     *
     * @param bool $value
     * @return mixed
     */
    public function setMocked(bool $value): static;

    /**
     * Set if a response has been cached or not.
     *
     * @param bool $value
     * @return mixed
     */
    public function setCached(bool $value): static;

    /**
     * Set the simulated response payload if the response was simulated.
     *
     * @param SimulatedResponsePayload $simulatedResponsePayload
     * @return mixed
     */
    public function setSimulatedResponsePayload(SimulatedResponsePayload $simulatedResponsePayload): static;

    /**
     * Get the simulated response payload if the response was simulated.
     *
     * @return simulatedResponsePayload|null
     */
    public function getSimulatedResponsePayload(): ?SimulatedResponsePayload;

    /**
     * Get the original sender exception
     *
     * @return Throwable|null
     */
    public function getSenderException(): ?Throwable;

    /**
     * Get the raw response
     *
     * @return mixed
     */
    public function getRawResponse(): mixed;

    /**
     * Get a header from the response.
     *
     * @param string $header
     * @return string|array|null
     */
    public function header(string $header): string|array|null;

    /**
     * Close the stream and any underlying resources.
     *
     * @return $this
     */
    public function close(): static;
}

<?php

namespace Sammyjo20\Saloon\Contracts;

use Exception;
use SimpleXMLElement;
use Illuminate\Support\Collection;
use Psr\Http\Message\StreamInterface;
use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Http\PendingSaloonRequest;
use Sammyjo20\Saloon\Repositories\ArrayRepository;
use Sammyjo20\Saloon\Exceptions\SaloonRequestException;

interface SaloonResponse
{
    /**
     * @return PendingSaloonRequest
     */
    public function getPendingSaloonRequest(): PendingSaloonRequest;

    /**
     * Get the original request
     *
     * @return SaloonRequest
     */
    public function getRequest(): SaloonRequest;

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
    public function collect(string $key = null): Collection;

    /**
     * Cast the response to a DTO.
     *
     * @return object|null
     */
    public function dto(): mixed;

    /**
     * Get a header from the response.
     *
     * @param string $header
     * @return string|null
     */
    public function header(string $header): ?string;

    /**
     * Get the headers from the response.
     *
     * @return ArrayRepository
     */
    public function headers(): ArrayRepository;

    /**
     * Get the status code of the response.
     *
     * @return int
     */
    public function status(): int;

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
     * Close the stream and any underlying resources.
     *
     * @return $this
     */
    public function close(): static;

    /**
     * Get the underlying PSR response for the response.
     *
     * @return mixed
     */
    public function toPsrResponse(): mixed;

    /**
     * Create an exception if a server or client error occurred.
     *
     * @return Exception|null
     */
    public function toException(): ?Exception;

    /**
     * Throw an exception if a server or client error occurred.
     *
     * @return $this
     * @throws SaloonRequestException
     */
    public function throw(): static;

    /**
     * Set if the response is cached. Should only be used internally.
     *
     * @param bool $cached
     * @return $this
     */
    public function setCached(bool $cached): static;

    /**
     * Set if the response is mocked. Should only be used internally.
     *
     * @param bool $mocked
     * @return $this
     */
    public function setMocked(bool $mocked): static;

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
     * Get the original request exception
     *
     * @return Exception|null
     */
    public function getRequestException(): ?Exception;

    /**
     * Get the body of the response.
     *
     * @return string
     */
    public function __toString(): string;
}

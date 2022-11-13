<?php declare(strict_types=1);

namespace Saloon\Contracts;

use Exception;
use SimpleXMLElement;
use Saloon\Http\Request;
use Saloon\Http\PendingRequest;
use Illuminate\Support\Collection;
use Saloon\Repositories\ArrayStore;
use Psr\Http\Message\StreamInterface;
use Psr\Http\Message\ResponseInterface;
use Saloon\Exceptions\RequestException;

interface Response
{
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
     * Create a new response instance.
     *
     * @param PendingRequest $pendingSaloonRequest
     * @param mixed $rawResponse
     * @param Exception|null $requestException
     */
    public function __construct(PendingRequest $pendingSaloonRequest, mixed $rawResponse, Exception $requestException = null);

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
     * @throws \JsonException
     */
    public function json(string $key = null, mixed $default = null): mixed;

    /**
     * Get the JSON decoded body of the response as an object.
     *
     * @return object
     * @throws \JsonException
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
     * @throws \JsonException
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
     * @return Exception|null
     */
    public function toException(): ?Exception;

    /**
     * Throw an exception if a server or client error occurred.
     *
     * @return $this
     * @throws RequestException
     */
    public function throw(): static;

    /**
     * Get the body of the response.
     *
     * @return string
     */
    public function __toString(): string;

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
     * Get the raw response
     *
     * @return mixed
     */
    public function getRawResponse(): mixed;

    /**
     * Get a header from the response.
     *
     * @param string $header
     * @return string|null
     */
    public function header(string $header): ?string;

    /**
     * Close the stream and any underlying resources.
     *
     * @return $this
     * @throws \JsonException
     */
    public function close(): static;

    /**
     * Set the isCached property
     *
     * @param bool $isCached
     * @return Response
     */
    public function setIsCached(bool $isCached): Response;

    /**
     * Set the isMocked property
     *
     * @param bool $isMocked
     * @return Response
     */
    public function setIsMocked(bool $isMocked): Response;
}

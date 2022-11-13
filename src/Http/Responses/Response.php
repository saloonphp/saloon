<?php declare(strict_types=1);

namespace Saloon\Http\Responses;

use Exception;
use SimpleXMLElement;
use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Illuminate\Support\Traits\Macroable;
use Saloon\Http\Request;
use Symfony\Component\DomCrawler\Crawler;
use Saloon\Http\PendingRequest;
use Saloon\Exceptions\SaloonRequestException;
use Saloon\Contracts\Response as ResponseContract;

abstract class Response implements ResponseContract
{
    use Macroable;

    /**
     * The decoded JSON response.
     *
     * @var array
     */
    protected array $decodedJson;

    /**
     * The decoded XML response.
     *
     * @var string
     */
    protected string $decodedXml;

    /**
     * The request options we attached to the request.
     *
     * @var PendingRequest
     */
    protected PendingRequest $pendingSaloonRequest;

    /**
     * The original request exception
     *
     * @var Exception|null
     */
    protected ?Exception $requestException = null;

    /**
     * Determines if the response has been cached
     *
     * @var bool
     */
    private bool $isCached = false;

    /**
     * Determines if the response has been mocked.
     *
     * @var bool
     */
    private bool $isMocked = false;

    /**
     * Create a new response instance.
     *
     * @param PendingRequest $pendingSaloonRequest
     * @param mixed $rawResponse
     * @param Exception|null $requestException
     */
    public function __construct(PendingRequest $pendingSaloonRequest, mixed $rawResponse, Exception $requestException = null)
    {
        $this->pendingSaloonRequest = $pendingSaloonRequest;
        $this->rawResponse = $rawResponse;
        $this->requestException = $requestException;
    }

    /**
     * @return PendingRequest
     */
    public function getPendingRequest(): PendingRequest
    {
        return $this->pendingSaloonRequest;
    }

    /**
     * Get the original request
     *
     * @return Request
     */
    public function getRequest(): Request
    {
        return $this->pendingSaloonRequest->getRequest();
    }

    /**
     * Get the JSON decoded body of the response as an array or scalar value.
     *
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     * @throws \JsonException
     */
    public function json(string $key = null, mixed $default = null): mixed
    {
        if (! isset($this->decodedJson)) {
            $this->decodedJson = json_decode($this->body(), true, 512, JSON_THROW_ON_ERROR);
        }

        if (is_null($key)) {
            return $this->decodedJson;
        }

        return Arr::get($this->decodedJson, $key, $default);
    }

    /**
     * Get the JSON decoded body of the response as an object.
     *
     * @return object
     * @throws \JsonException
     */
    public function object(): object
    {
        return json_decode($this->body(), false, 512, JSON_THROW_ON_ERROR);
    }

    /**
     * Convert the XML response into a SimpleXMLElement.
     *
     * @param ...$arguments
     * @return SimpleXMLElement|bool
     */
    public function xml(...$arguments): SimpleXMLElement|bool
    {
        if (! $this->decodedXml) {
            $this->decodedXml = $this->body();
        }

        return simplexml_load_string($this->decodedXml, ...$arguments);
    }

    /**
     * Get the JSON decoded body of the response as a collection.
     *
     * @param $key
     * @return Collection
     * @throws \JsonException
     */
    public function collect($key = null): Collection
    {
        return Collection::make($this->json($key));
    }

    /**
     * Cast the response to a DTO.
     *
     * @return mixed
     */
    public function dto(): mixed
    {
        if ($this->failed()) {
            return null;
        }

        return $this->getRequest()->createDtoFromResponse($this);
    }

    /**
     * Parse the HTML or XML body into a Symfony DomCrawler instance.
     *
     * Requires Symfony Crawler (composer require symfony/dom-crawler)
     * @see https://symfony.com/doc/current/components/dom_crawler.html
     *
     * @return Crawler
     */
    public function dom(): Crawler
    {
        return new Crawler($this->body());
    }

    /**
     * Determine if the request was successful.
     *
     * @return bool
     */
    public function successful(): bool
    {
        return $this->status() >= 200 && $this->status() < 300;
    }

    /**
     * Determine if the response code was "OK".
     *
     * @return bool
     */
    public function ok(): bool
    {
        return $this->status() === 200;
    }

    /**
     * Determine if the response was a redirect.
     *
     * @return bool
     */
    public function redirect(): bool
    {
        return $this->status() >= 300 && $this->status() < 400;
    }

    /**
     * Determine if the response indicates a client or server error occurred.
     *
     * @return bool
     */
    public function failed(): bool
    {
        return $this->serverError() || $this->clientError();
    }

    /**
     * Determine if the response indicates a client error occurred.
     *
     * @return bool
     */
    public function clientError(): bool
    {
        return $this->status() >= 400 && $this->status() < 500;
    }

    /**
     * Determine if the response indicates a server error occurred.
     *
     * @return bool
     */
    public function serverError(): bool
    {
        return $this->status() >= 500;
    }

    /**
     * Execute the given callback if there was a server or client error.
     *
     * @param callable $callback
     * @return $this
     */
    public function onError(callable $callback): static
    {
        if ($this->failed()) {
            $callback($this);
        }

        return $this;
    }

    /**
     * Create an exception if a server or client error occurred.
     *
     * @return Exception|null
     */
    public function toException(): ?Exception
    {
        if ($this->successful()) {
            return null;
        }

        return $this->createException($this->body());
    }

    /**
     * Create the request exception
     *
     * @param string $body
     * @return Exception
     */
    protected function createException(string $body): Exception
    {
        return new SaloonRequestException($this, $body, 0, $this->getRequestException());
    }

    /**
     * Throw an exception if a server or client error occurred.
     *
     * @return $this
     * @throws SaloonRequestException
     */
    public function throw(): static
    {
        if ($this->failed()) {
            throw $this->toException();
        }

        return $this;
    }

    /**
     * Get the body of the response.
     *
     * @return string
     */
    public function __toString(): string
    {
        return $this->body();
    }

    /**
     * Set if the response is cached. Should only be used internally.
     *
     * @param bool $cached
     * @return $this
     */
    public function setCached(bool $cached): static
    {
        $this->isCached = $cached;

        return $this;
    }

    /**
     * Set if the response is mocked. Should only be used internally.
     *
     * @param bool $mocked
     * @return $this
     */
    public function setMocked(bool $mocked): static
    {
        $this->isMocked = $mocked;

        return $this;
    }

    /**
     * Check if the response has been cached
     *
     * @return bool
     */
    public function isCached(): bool
    {
        return $this->isCached;
    }

    /**
     * Check if the response has been mocked
     *
     * @return bool
     */
    public function isMocked(): bool
    {
        return $this->isMocked;
    }

    /**
     * Get the original request exception
     *
     * @return Exception|null
     */
    public function getRequestException(): ?Exception
    {
        return $this->requestException;
    }

    /**
     * Get the raw response
     *
     * @return mixed
     */
    public function getRawResponse(): mixed
    {
        return $this->rawResponse;
    }

    /**
     * Get a header from the response.
     *
     * @param string $header
     * @return string|null
     */
    public function header(string $header): ?string
    {
        return $this->headers()->get($header);
    }

    /**
     * Close the stream and any underlying resources.
     *
     * @return $this
     * @throws \JsonException
     */
    public function close(): static
    {
        $this->stream()->close();

        return $this;
    }

    /**
     * Set the isCached property
     *
     * @param bool $isCached
     * @return Response
     */
    public function setIsCached(bool $isCached): Response
    {
        $this->isCached = $isCached;

        return $this;
    }

    /**
     * Set the isMocked property
     *
     * @param bool $isMocked
     * @return Response
     */
    public function setIsMocked(bool $isMocked): Response
    {
        $this->isMocked = $isMocked;

        return $this;
    }
}

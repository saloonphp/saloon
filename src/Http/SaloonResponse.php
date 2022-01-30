<?php

namespace Sammyjo20\Saloon\Http;

use Illuminate\Support\Arr;
use GuzzleHttp\Psr7\Response;
use GuzzleHttp\Cookie\CookieJar;
use Illuminate\Support\Collection;
use Illuminate\Support\Traits\Macroable;
use GuzzleHttp\Exception\RequestException;
use Sammyjo20\Saloon\Exceptions\SaloonRequestException;

class SaloonResponse
{
    use Macroable;

    /**
     * The underlying PSR response.
     *
     * @var Response
     */
    protected Response $response;

    /**
     * The decoded JSON response.
     *
     * @var array
     */
    protected $decoded;

    /**
     * The request options we attached to the request.
     *
     * @var array
     */
    protected array $requestOptions;

    /**
     * The original request
     *
     * @var SaloonRequest
     */
    protected SaloonRequest $originalRequest;

    /**
     * Should we attempt to guess the status of the request from the body?
     *
     * @var bool
     */
    protected bool $guessesStatusFromBody = false;

    /**
     * The original Guzzle request exception
     *
     * @var RequestException|null
     */
    protected ?RequestException $guzzleRequestException = null;

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
     * @param array $requestOptions
     * @param SaloonRequest $originalRequest
     * @param Response $response
     * @param RequestException|null $guzzleRequestException
     */
    public function __construct(array $requestOptions, SaloonRequest $originalRequest, Response $response, RequestException $guzzleRequestException = null)
    {
        $this->requestOptions = $requestOptions;
        $this->originalRequest = $originalRequest;
        $this->response = $response;
        $this->guzzleRequestException = $guzzleRequestException;
    }

    /**
     * Get the request options we attached to the request. Headers, Config etc.
     *
     * @return array
     */
    public function getRequestOptions(): array
    {
        return $this->requestOptions;
    }

    /**
     * Get the original request
     *
     * @return SaloonRequest
     */
    public function getOriginalRequest(): SaloonRequest
    {
        return $this->originalRequest;
    }

    /**
     * Get the body of the response.
     *
     * @return string
     */
    public function body()
    {
        return (string)$this->response->getBody();
    }

    /**
     * Get the JSON decoded body of the response as an array or scalar value.
     *
     * @param string|null $key
     * @param mixed $default
     * @return mixed
     */
    public function json($key = null, $default = null)
    {
        if (! $this->decoded) {
            $this->decoded = json_decode($this->body(), true);
        }

        if (is_null($key)) {
            return $this->decoded;
        }

        return Arr::get($this->decoded, $key, $default);
    }

    /**
     * Get the JSON decoded body of the response as an object.
     *
     * @return object
     */
    public function object()
    {
        return json_decode($this->body(), false);
    }

    /**
     * Get the JSON decoded body of the response as a collection.
     *
     * @param $key
     * @return Collection
     */
    public function collect($key = null): Collection
    {
        return Collection::make($this->json($key));
    }

    /**
     * Get a header from the response.
     *
     * @param string $header
     * @return string
     */
    public function header(string $header): string
    {
        return $this->response->getHeaderLine($header);
    }

    /**
     * Get the headers from the response.
     *
     * @return array
     */
    public function headers(): array
    {
        return $this->response->getHeaders();
    }

    /**
     * Get the status code of the response.
     *
     * @return int
     */
    private function getStatusFromResponse(): int
    {
        return (int)$this->response->getStatusCode();
    }

    /**
     * Get the status code of the response.
     *
     * @return int
     */
    public function status(): int
    {
        if ($this->guessesStatusFromBody === true) {
            return $this->guessStatusFromBody();
        }

        return $this->getStatusFromResponse();
    }

    /**
     * Attempt to guess the status code from the body.
     *
     * Basically check it against a regex, then check if that string is
     * numeric, and if so - return it as an integer.
     *
     * @return int
     */
    private function guessStatusFromBody(): int
    {
        $status = $this->json('status');

        if (preg_match('/^[1-5][0-9][0-9]$/', $status)) {
            return (int)$status;
        }

        return $this->getStatusFromResponse();
    }

    /**
     * Determine if the request was successful.
     *
     * @return bool
     */
    public function successful()
    {
        return $this->status() >= 200 && $this->status() < 300;
    }

    /**
     * Determine if the response code was "OK".
     *
     * @return bool
     */
    public function ok()
    {
        return $this->status() === 200;
    }

    /**
     * Determine if the response was a redirect.
     *
     * @return bool
     */
    public function redirect()
    {
        return $this->status() >= 300 && $this->status() < 400;
    }

    /**
     * Determine if the response indicates a client or server error occurred.
     *
     * @return bool
     */
    public function failed()
    {
        return $this->serverError() || $this->clientError();
    }

    /**
     * Determine if the response indicates a client error occurred.
     *
     * @return bool
     */
    public function clientError()
    {
        return $this->status() >= 400 && $this->status() < 500;
    }

    /**
     * Determine if the response indicates a server error occurred.
     *
     * @return bool
     */
    public function serverError()
    {
        return $this->status() >= 500;
    }

    /**
     * Execute the given callback if there was a server or client error.
     *
     * @param callable $callback
     * @return $this
     */
    public function onError(callable $callback): self
    {
        if ($this->failed()) {
            $callback($this);
        }

        return $this;
    }

    /**
     * Close the stream and any underlying resources.
     *
     * @return $this
     */
    public function close(): self
    {
        $this->response->getBody()->close();

        return $this;
    }

    /**
     * Get the underlying PSR response for the response.
     *
     * @return Response
     */
    public function toPsrResponse(): Response
    {
        return $this->response;
    }

    /**
     * Get the underlying PSR response for the response.
     *
     * @return Response
     */
    public function toGuzzleResponse(): Response
    {
        return $this->toPsrResponse();
    }

    /**
     * Create an exception if a server or client error occurred.
     *
     * @return SaloonRequestException|void
     */
    public function toException()
    {
        if ($this->failed()) {
            $body = $this->response?->getBody()?->getContents();

            return new SaloonRequestException($this, $body, 0, $this->guzzleRequestException);
        }
    }

    /**
     * Throw an exception if a server or client error occurred.
     *
     * @return $this
     * @throws SaloonRequestException
     */
    public function throw()
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
    public function __toString()
    {
        return $this->body();
    }

    /**
     * Set if the response is cached. Should only be used internally.
     *
     * @param bool $cached
     * @return $this
     */
    public function setCached(bool $cached): self
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
    public function setMocked(bool $mocked): self
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
     * @return RequestException|null
     */
    public function getGuzzleException(): ?RequestException
    {
        return $this->guzzleRequestException;
    }

    /**
     * Should the response guess the status from the body?
     *
     * @return $this
     */
    public function guessesStatusFromBody(): self
    {
        $this->guessesStatusFromBody = true;

        return $this;
    }
}

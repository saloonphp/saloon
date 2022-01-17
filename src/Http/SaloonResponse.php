<?php

namespace Sammyjo20\Saloon\Http;

use Illuminate\Support\Arr;
use Illuminate\Support\Collection;
use Sammyjo20\Saloon\Exceptions\SaloonRequestException;

class SaloonResponse
{
    /**
     * The underlying PSR response.
     *
     * @var \Psr\Http\Message\ResponseInterface
     */
    protected $response;

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
    protected array $saloonRequestOptions;

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
    protected bool $shouldGuessStatusFromBody = false;

    /**
     * Determines if the response has been cached
     *
     * @var bool
     */
    private bool $isCached = false;

    /**
     * Create a new response instance.
     *
     * @param array $requestOptions
     * @param SaloonRequest $originalRequest
     * @param $response
     * @param bool $shouldGuessStatusFromBody
     */
    public function __construct(array $requestOptions, SaloonRequest $originalRequest, $response, bool $shouldGuessStatusFromBody = false)
    {
        $this->saloonRequestOptions = $requestOptions;
        $this->originalRequest = $originalRequest;
        $this->response = $response;
        $this->shouldGuessStatusFromBody = $shouldGuessStatusFromBody;
    }

    /**
     * Get the request options we attached to the request. Headers, Config etc.
     *
     * @return array
     */
    public function getSaloonRequestOptions(): array
    {
        return $this->saloonRequestOptions;
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
        return (string) $this->response->getBody();
    }

    /**
     * Get the JSON decoded body of the response as an array or scalar value.
     *
     * @param  string|null  $key
     * @param  mixed  $default
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
     * @param  string|null  $key
     * @return \Illuminate\Support\Collection
     */
    public function collect($key = null)
    {
        return Collection::make($this->json($key));
    }

    /**
     * Get a header from the response.
     *
     * @param  string  $header
     * @return string
     */
    public function header(string $header)
    {
        return $this->response->getHeaderLine($header);
    }

    /**
     * Get the headers from the response.
     *
     * @return array
     */
    public function headers()
    {
        return $this->response->getHeaders();
    }

    /**
     * Get the status code of the response.
     *
     * @return int
     */
    public function getStatusFromResponse(): int
    {
        return (int) $this->response->getStatusCode();
    }

    /**
     * Get the status code of the response.
     *
     * @return int
     */
    public function status()
    {
        if ($this->shouldGuessStatusFromBody === true) {
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
    public function guessStatusFromBody(): int
    {
        $body = $this->json('status', null);

        if (isset($body) === false) {
            return $this->getStatusFromResponse();
        }

        if (! preg_match('/^[1-5][0-9][0-9]$/', $body)) {
            return $this->getStatusFromResponse();
        }

        if (is_numeric($body) === false) {
            return $this->getStatusFromResponse();
        }

        return (int)$body;
    }

    /**
     * Get the effective URI of the response.
     *
     * @return \Psr\Http\Message\UriInterface|null
     */
    public function effectiveUri()
    {
        return optional($this->transferStats)->getEffectiveUri();
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
     * @param  callable  $callback
     * @return $this
     */
    public function onError(callable $callback)
    {
        if ($this->failed()) {
            $callback($this);
        }

        return $this;
    }

    /**
     * Get the response cookies.
     *
     * @return \GuzzleHttp\Cookie\CookieJar
     */
    public function cookies()
    {
        return $this->cookies;
    }

    /**
     * Get the handler stats of the response.
     *
     * @return array
     */
    public function handlerStats()
    {
        return optional($this->transferStats)->getHandlerStats() ?? [];
    }

    /**
     * Close the stream and any underlying resources.
     *
     * @return $this
     */
    public function close()
    {
        $this->response->getBody()->close();

        return $this;
    }

    /**
     * Get the underlying PSR response for the response.
     *
     * @return \Psr\Http\Message\ResponseInterface
     */
    public function toPsrResponse()
    {
        return $this->response;
    }

    /**
     * Create an exception if a server or client error occurred.
     *
     * @return SaloonRequestException|void
     */
    public function toException()
    {
        if ($this->failed()) {
            return new SaloonRequestException($this, $this->response?->getBody()?->getContents());
        }
    }

    /**
     * Throw an exception if a server or client error occurred.
     *
     * @return $this
     * @throws SaloonException
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
     * Check if the response has been cached
     *
     * @return bool
     */
    public function isCached(): bool
    {
        return $this->isCached;
    }

    /**
     * Dynamically proxy other methods to the underlying response.
     *
     * @param  string  $method
     * @param  array  $parameters
     * @return mixed
     */
    public function __call($method, $parameters)
    {
        return $this->response->{$method}(...$parameters);
    }
}

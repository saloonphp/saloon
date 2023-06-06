<?php

namespace Sammyjo20\Saloon\Http;

use Throwable;
use GuzzleHttp\Psr7\Response;
use Psr\Http\Message\RequestInterface;
use Sammyjo20\Saloon\Traits\CollectsData;
use Sammyjo20\Saloon\Traits\CollectsConfig;
use Sammyjo20\Saloon\Traits\CollectsHeaders;
use Sammyjo20\Saloon\Traits\Plugins\HasBody;
use Sammyjo20\Saloon\Data\MockExceptionClosure;
use Sammyjo20\Saloon\Exceptions\DirectoryNotFoundException;

class MockResponse
{
    use CollectsHeaders,
        CollectsConfig,
        CollectsData;

    /**
     * @var int
     */
    protected int $status;

    /**
     * @var mixed
     */
    protected mixed $rawData = null;

    /**
     * @var MockExceptionClosure|null
     */
    protected ?MockExceptionClosure $exceptionClosure = null;

    /**
     * Create a new mock response
     *
     * @param int $status
     * @param array $data
     * @param array $headers
     * @param array $config
     */
    public function __construct(mixed $data = [], int $status = 200, array $headers = [], array $config = [])
    {
        $this->status = $status;

        // If the data type is an array, we'll assume that it should be JSON data.
        // of course - if content-type is passed into $headers, it will replace this
        // default.

        if (is_array($data)) {
            $this->mergeData($data)->addHeader('Content-Type', 'application/json');
        } else {
            $this->rawData = $data;
        }

        $this->mergeHeaders($headers)->mergeConfig($config);
    }

    /**
     * Create a new mock response
     *
     * @param mixed $data
     * @param int $status
     * @param array $headers
     * @param array $config
     * @return static
     */
    public static function make(mixed $data = [], int $status = 200, array $headers = [], array $config = []): self
    {
        return new static($data, $status, $headers, $config);
    }

    /**
     * Create a new mock response from a Saloon request.
     *
     * @param SaloonRequest $request
     * @param int $status
     * @return static
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonInvalidConnectorException
     */
    public static function fromRequest(SaloonRequest $request, int $status = 200): self
    {
        // Let's try to work out where we should pull in the data in. If the request uses
        // the HasBody trait, that means it's going to be raw data - so we'll just grab that
        // raw data. Otherwise, use the normal "getData" method.

        $data = array_key_exists(HasBody::class, class_uses($request)) && method_exists($request, 'defineBody')
            ? $request->defineBody()
            : $request->getData();

        return new static($data, $status, $request->getHeaders(), $request->getConfig());
    }

    /**
     * Create a new mock response from a fixture
     *
     * @param string $name
     * @return Fixture
     * @throws DirectoryNotFoundException
     */
    public static function fixture(string $name): Fixture
    {
        return new Fixture($name);
    }

    /**
     * Throw an exception on the request.
     *
     * @param callable|Throwable $value
     * @return $this
     */
    public function throw(callable|Throwable $value): self
    {
        $closure = $value instanceof Throwable ? static fn () => $value : $value;

        $this->exceptionClosure = new MockExceptionClosure($closure);

        return $this;
    }

    /**
     * Get the status from the responses
     *
     * @return int
     */
    public function getStatus(): int
    {
        return $this->status;
    }

    /**
     * Get the formatted data on the response.
     *
     * @return mixed
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonInvalidConnectorException
     */
    public function getFormattedData(): mixed
    {
        if (isset($this->rawData)) {
            return $this->rawData;
        }

        $data = $this->getData();

        if (is_array($data) && $this->getHeader('Content-Type') == 'application/json') {
            return json_encode($data);
        }

        if (empty($data)) {
            return null;
        }

        return $data;
    }

    /**
     * Convert the mock response into a Guzzle response
     *
     * @return Response
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonInvalidConnectorException
     */
    public function toGuzzleResponse(): Response
    {
        return new Response($this->getStatus(), $this->getHeaders(), $this->getFormattedData());
    }

    /**
     * Checks if the response throws an exception.
     *
     * @return bool
     */
    public function throwsException(): bool
    {
        return $this->exceptionClosure instanceof MockExceptionClosure;
    }

    /**
     * Retrieve the exception.
     *
     * @param RequestInterface $psrRequest
     * @return Throwable
     */
    public function getException(RequestInterface $psrRequest): Throwable
    {
        return $this->exceptionClosure->call($psrRequest);
    }
}

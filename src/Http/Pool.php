<?php declare(strict_types=1);

namespace Saloon\Http;

use Generator;
use GuzzleHttp\Promise\EachPromise;
use GuzzleHttp\Promise\PromiseInterface;
use Saloon\Contracts\Pool as PoolContract;
use Saloon\Exceptions\InvalidPoolItemException;

class Pool implements PoolContract
{
    /**
     * Requests inside the pool
     *
     * @var Generator
     */
    protected Generator $requests;

    /**
     * Handle Response Callback
     *
     * @var callable
     */
    protected mixed $responseHandler = null;

    /**
     * Handle Exception Callback
     *
     * @var callable
     */
    protected mixed $exceptionHandler = null;

    /**
     * Connector
     *
     * @var Connector
     */
    protected Connector $connector;

    /**
     * Concurrency
     *
     * How many requests will be sent at once.
     *
     * @var int|callable
     */
    protected mixed $concurrency;

    /**
     * Constructor
     *
     * @param \Saloon\Http\Connector $connector
     * @param callable|iterable $requestPayload
     * @param int|callable $concurrency
     * @param callable|null $responseHandler
     * @param callable|null $exceptionHandler
     */
    public function __construct(Connector $connector, callable|iterable $requestPayload = [], int|callable $concurrency = 5, callable|null $responseHandler = null, callable|null $exceptionHandler = null)
    {
        $this->connector = $connector;
        $this->setRequests($requestPayload);
        $this->concurrency = $concurrency;
        $this->responseHandler = $responseHandler;
        $this->exceptionHandler = $exceptionHandler;
    }

    /**
     * Specify a callback to happen for each successful request
     *
     * @param callable $callable
     * @return $this
     */
    public function withResponseHandler(callable $callable): static
    {
        $this->responseHandler = $callable;

        return $this;
    }

    /**
     * Specify a callback to happen for each failed request
     *
     * @param callable $callable
     * @return $this
     */
    public function withExceptionHandler(callable $callable): static
    {
        $this->exceptionHandler = $callable;

        return $this;
    }

    /**
     * Set the amount of concurrent requests that should be sent
     *
     * @param int|callable $concurrency
     * @return Pool
     */
    public function setConcurrency(int|callable $concurrency): Pool
    {
        $this->concurrency = $concurrency;

        return $this;
    }

    /**
     * Set the requests
     *
     * @param callable|iterable $requests
     * @return $this
     */
    public function setRequests(callable|iterable $requests): Pool
    {
        if (is_callable($requests)) {
            $requests = $requests($this->connector);
        }

        if (is_iterable($requests)) {
            $requests = static fn (): Generator => yield from $requests;
        }

        $this->requests = $requests();

        return $this;
    }

    /**
     * Get the request generator
     *
     * @return Generator
     */
    public function getRequests(): Generator
    {
        return $this->requests;
    }

    /**
     * Send the pool and create a Promise
     *
     * @return \GuzzleHttp\Promise\PromiseInterface
     * @throws \Saloon\Exceptions\InvalidPoolItemException
     * @throws \Saloon\Exceptions\SaloonException
     */
    public function send(): PromiseInterface
    {
        // Iterate through the existing generator and "prepare" the requests.
        // If they are SaloonRequests then we should convert them into
        // promises.

        $preparedRequests = function (): Generator {
            foreach ($this->requests as $key => $request) {
                match (true) {
                    $request instanceof Request => yield $key => $this->connector->sendAsync($request),
                    $request instanceof PromiseInterface => yield $key => $request,
                    default => throw new InvalidPoolItemException
                };
            }
        };

        // Next we'll use an EachPromise which accepts an iterator of
        // requests and will process them as the concurrency we set.

        $eachPromise = new EachPromise($preparedRequests(), [
            'concurrency' => $this->concurrency,
            'fulfilled' => $this->responseHandler,
            'rejected' => $this->exceptionHandler,
        ]);

        return $eachPromise->promise();
    }
}

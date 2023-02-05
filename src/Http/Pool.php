<?php

declare(strict_types=1);

namespace Saloon\Http;

use Closure;
use Generator;
use Saloon\Contracts\Connector;
use GuzzleHttp\Promise\EachPromise;
use GuzzleHttp\Promise\PromiseInterface;
use Saloon\Contracts\Pool as PoolContract;
use Saloon\Exceptions\InvalidPoolItemException;

class Pool implements PoolContract
{
    /**
     * Requests inside the pool
     *
     * @var iterable<\GuzzleHttp\Promise\PromiseInterface|\Saloon\Contracts\Request>
     */
    protected iterable $requests;

    /**
     * Handle Response Callback
     *
     * @var \Closure(\Saloon\Contracts\Response, array-key, \GuzzleHttp\Promise\PromiseInterface): (void)|null
     */
    protected ?Closure $responseHandler = null;

    /**
     * Handle Exception Callback
     *
     * @var \Closure(mixed, array-key, \GuzzleHttp\Promise\PromiseInterface): (void)|null
     */
    protected ?Closure $exceptionHandler = null;

    /**
     * Connector
     *
     * @var \Saloon\Contracts\Connector
     */
    protected Connector $connector;

    /**
     * Concurrency
     *
     * How many requests will be sent at once.
     *
     * @var int|\Closure(int): int
     */
    protected int|Closure $concurrency;

    /**
     * Constructor
     *
     * @param \Saloon\Http\Connector $connector
     * @param iterable<\GuzzleHttp\Promise\PromiseInterface|\Saloon\Contracts\Request>|callable(\Saloon\Contracts\Connector): iterable<\GuzzleHttp\Promise\PromiseInterface|\Saloon\Contracts\Request> $requests
     * @param int|callable(int $pendingRequests): (int) $concurrency
     * @param callable(\Saloon\Contracts\Response, array-key $key, \GuzzleHttp\Promise\PromiseInterface $poolAggregate): (void)|null $responseHandler
     * @param callable(mixed $reason, array-key $key, \GuzzleHttp\Promise\PromiseInterface $poolAggregate): (void)|null $exceptionHandler
     */
    public function __construct(Connector $connector, iterable|callable $requests = [], int|callable $concurrency = 5, callable|null $responseHandler = null, callable|null $exceptionHandler = null)
    {
        $this->connector = $connector;
        $this->setRequests($requests);
        $this->setConcurrency($concurrency);

        if (! is_null($responseHandler)) {
            $this->withResponseHandler($responseHandler);
        }

        if (! is_null($exceptionHandler)) {
            $this->withExceptionHandler($exceptionHandler);
        }
    }

    /**
     * Specify a callback to happen for each successful request
     *
     * @param callable(\Saloon\Contracts\Response, array-key $key, \GuzzleHttp\Promise\PromiseInterface $poolAggregate): (void) $callable
     * @return $this
     */
    public function withResponseHandler(callable $callable): static
    {
        $this->responseHandler = $callable(...);

        return $this;
    }

    /**
     * Specify a callback to happen for each failed request
     *
     * @param callable(mixed $reason, array-key $key, \GuzzleHttp\Promise\PromiseInterface $poolAggregate): (void) $callable
     * @return $this
     */
    public function withExceptionHandler(callable $callable): static
    {
        $this->exceptionHandler = $callable(...);

        return $this;
    }

    /**
     * Set the amount of concurrent requests that should be sent
     *
     * @param int|callable(int $pendingRequests): (int) $concurrency
     * @return $this
     */
    public function setConcurrency(int|callable $concurrency): static
    {
        $this->concurrency = is_callable($concurrency) ? $concurrency(...) : $concurrency;

        return $this;
    }

    /**
     * Set the requests
     *
     * @param iterable<\GuzzleHttp\Promise\PromiseInterface|\Saloon\Contracts\Request>|callable(\Saloon\Contracts\Connector): iterable<\GuzzleHttp\Promise\PromiseInterface|\Saloon\Contracts\Request> $requests
     * @return $this
     */
    public function setRequests(iterable|callable $requests): static
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
     * @return iterable<\GuzzleHttp\Promise\PromiseInterface|\Saloon\Contracts\Request>
     */
    public function getRequests(): iterable
    {
        return $this->requests;
    }

    /**
     * Send the pool and create a Promise
     *
     * @return \GuzzleHttp\Promise\PromiseInterface
     * @throws \ReflectionException
     * @throws \Saloon\Exceptions\InvalidPoolItemException
     * @throws \Saloon\Exceptions\InvalidResponseClassException
     * @throws \Saloon\Exceptions\PendingRequestException
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

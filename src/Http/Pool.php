<?php

namespace Sammyjo20\Saloon\Http;

use Closure;
use Generator;
use GuzzleHttp\Promise\EachPromise;
use GuzzleHttp\Promise\PromiseInterface;
use Sammyjo20\Saloon\Exceptions\InvalidPoolItemException;

class Pool
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
    protected mixed $onResponse = null;

    /**
     * Handle Exception Callback
     *
     * @var callable
     */
    protected mixed $onException = null;

    /**
     * Connector
     *
     * @var SaloonConnector
     */
    protected SaloonConnector $connector;

    /**
     * Concurrent Requests
     *
     * @var int
     */
    protected int $concurrentRequests;

    /**
     * Constructor
     *
     * @param SaloonConnector $connector
     * @param array|Generator|Closure|callable $requestPayload
     * @param int $concurrentRequests
     * @throws InvalidPoolItemException
     * @throws \ReflectionException
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonException
     */
    public function __construct(SaloonConnector $connector, iterable|Generator|Closure|callable $requestPayload = [], int $concurrentRequests = 5)
    {
        $this->connector = $connector;
        $this->setRequests($requestPayload);
        $this->concurrentRequests = $concurrentRequests;
    }

    /**
     * Specify a callback to happen for each successful request
     *
     * @param callable $callable
     * @return $this
     */
    public function handleResponse(callable $callable): static
    {
        $this->onResponse = $callable;

        return $this;
    }

    /**
     * Specify a callback to happen for each failed request
     *
     * @param callable $callable
     * @return $this
     */
    public function handleException(callable $callable): static
    {
        $this->onException = $callable;

        return $this;
    }

    /**
     * Set the amount of concurrent requests that should be sent
     *
     * @param int $concurrentRequests
     * @return Pool
     */
    public function setConcurrentRequests(int $concurrentRequests): Pool
    {
        $this->concurrentRequests = $concurrentRequests;

        return $this;
    }

    /**
     * Set the requests
     *
     * @param array|Generator|callable<iterable> $requests
     * @return $this
     * @throws InvalidPoolItemException
     * @throws \ReflectionException
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonException
     */
    public function setRequests(iterable|Generator|callable $requests): Pool
    {
        if (is_callable($requests)) {
            $requests = $requests();
        }

        if (is_iterable($requests)) {
            $requests = static fn(): Generator => yield from $requests;
        }

        $requests = function () use ($requests) {
            foreach ($requests() as $request) {
                if ($request instanceof PromiseInterface) {
                    yield $request;
                    continue;
                }

                if ($request instanceof SaloonRequest) {
                    yield $this->connector->sendAsync($request);
                    continue;
                }

                throw new InvalidPoolItemException;
            }
        };

        $this->requests = $requests();

        return $this;
    }

    /**
     * Send the pool and create a Promise
     *
     * @return PromiseInterface
     */
    public function send(): PromiseInterface
    {
        $requests = $this->requests;

        $eachPromise = new EachPromise($requests, [
            'concurrency' => $this->concurrentRequests,
            'fulfilled' => $this->onResponse,
            'rejected' => $this->onException,
        ]);

        return $eachPromise->promise();
    }
}

<?php

namespace Sammyjo20\Saloon\Http;

use Closure;
use Generator;
use GuzzleHttp\Promise\EachPromise;
use GuzzleHttp\Promise\PromiseInterface;

class Pool
{
    /**
     * Requests inside the pool
     *
     * @var Generator
     */
    protected mixed $requestIterator;

    /**
     * Then Callback
     *
     * @var Closure|callable|null
     */
    protected mixed $onThen = null;

    /**
     * Catch Callback
     *
     * @var Closure|callable|null
     */
    protected mixed $onCatch = null;

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
     */
    public function __construct(SaloonConnector $connector, array|Generator|Closure|callable $requestPayload, int $concurrentRequests = 5)
    {
        $this->connector = $connector;
        $this->concurrentRequests = $concurrentRequests;

        $this->setRequestIterator($requestPayload);
    }

    /**
     * Specify a callback to happen for each successful request
     *
     * @param Closure|callable $callable
     * @return $this
     */
    public function then(Closure|callable $callable): static
    {
        $this->onThen = $callable;

        return $this;
    }

    /**
     * Specify a callback to happen for each failed request
     *
     * @param Closure|callable $callable
     * @return $this
     */
    public function catch(Closure|callable $callable): static
    {
        $this->onCatch = $callable;

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
     * @param array|Generator|Closure|callable $requestPayload
     * @return Pool
     */
    public function setRequestIterator(array|Generator|Closure|callable $requestPayload): Pool
    {
        if (is_callable($requestPayload)) {
            $requestPayload = $requestPayload();
        }

        if (is_array($requestPayload)) {
            $requestPayload = function () use ($requestPayload) {
                foreach ($requestPayload as $request) {
                    yield $this->connector->sendAsync($request);
                }
            };
        }

        $this->requestIterator = $requestPayload;

        return $this;
    }

    /**
     * Send the pool and create a Promise
     *
     * @return PromiseInterface
     * @throws \ReflectionException
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonException
     */
    public function send(): PromiseInterface
    {
        $generator = $this->requestIterator;

        $eachPromise = new EachPromise($generator(), [
            'concurrency' => $this->concurrentRequests,
            'fulfilled' => $this->onThen,
            'rejected' => $this->onCatch,
        ]);

        return $eachPromise->promise();
    }
}

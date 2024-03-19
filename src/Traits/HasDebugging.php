<?php

declare(strict_types=1);

namespace Saloon\Traits;

use Saloon\Http\Connector;
use Saloon\Http\Request;
use Saloon\Http\Response;
use Saloon\Enums\PipeOrder;
use Saloon\Http\PendingRequest;
use function Saloon\debug;

trait HasDebugging
{
    /**
     * Register a request debugger
     *
     * @param callable(\Saloon\Http\PendingRequest, \Psr\Http\Message\RequestInterface): void $onRequest
     * @return $this
     */
    public function debugRequest(callable $onRequest): static
    {
        $this->middleware()->onRequest(
            callable: static function (PendingRequest $pendingRequest) use ($onRequest): void {
                $onRequest($pendingRequest, $pendingRequest->createPsrRequest());
            },
            order: PipeOrder::LAST
        );

        return $this;
    }

    /**
     * Register a response debugger
     *
     * @param callable(\Saloon\Http\Response, \Psr\Http\Message\ResponseInterface): void $onResponse
     * @return $this
     */
    public function debugResponse(callable $onResponse): static
    {
        $this->middleware()->onResponse(
            callable: static function (Response $response) use ($onResponse): void {
                $onResponse($response, $response->getPsrResponse());
            },
            order: PipeOrder::FIRST
        );

        return $this;
    }

    /**
     * Dump a request before being sent and terminate the application
     *
     * Note that anything added by the sender will not be reflected here.
     *
     * Requires symfony/var-dumper
     */
    public function dd(): static
    {
        return debug($this, die: true);
    }

    /**
     * Dump a request before being sent
     *
     * Note that anything added by the sender will not be reflected here.
     *
     * Requires symfony/var-dumper
     */
    public function dump(): static
    {
        return debug($this, die: false);
    }
}

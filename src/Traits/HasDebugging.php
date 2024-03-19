<?php

declare(strict_types=1);

namespace Saloon\Traits;

use Saloon\Http\Request;
use Saloon\Http\Response;
use function Saloon\debug;
use Saloon\Enums\PipeOrder;
use Saloon\Http\PendingRequest;

trait HasDebugging
{
    /**
     * Register a request debugger
     *
     * @param callable(\Saloon\Http\PendingRequest, \Psr\Http\Message\RequestInterface): void|null $onRequest
     * @return $this
     */
    public function debugRequest(?callable $onRequest = null): static
    {
        if (is_null($onRequest)) {
            return debug($this, response: false, die: false);
        }

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
     * @param callable(\Saloon\Http\Response, \Psr\Http\Message\ResponseInterface): void|null $onResponse
     * @return $this
     */
    public function debugResponse(?callable $onResponse = null): static
    {
        if (is_null($onResponse)) {
            return debug($this, request: false, response: true, die: false);
        }

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

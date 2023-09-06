<?php

declare(strict_types=1);

namespace Saloon\Traits;

use Saloon\Data\PipeOrder;
use Saloon\Contracts\Response;
use Saloon\Contracts\PendingRequest;

trait HasDebugging
{
    /**
     * Register a request debugger
     *
     * @param callable(\Saloon\Contracts\PendingRequest, \Psr\Http\Message\RequestInterface): void $onRequest
     * @return $this
     */
    public function debugRequest(callable $onRequest): static
    {
        $this->middleware()->onRequest(
            callable: static function (PendingRequest $pendingRequest) use ($onRequest): void {
                $onRequest($pendingRequest, $pendingRequest->createPsrRequest());
            },
            order: PipeOrder::last()
        );

        return $this;
    }

    /**
     * Register a response debugger
     *
     * @param callable(\Saloon\Contracts\Response, \Psr\Http\Message\ResponseInterface): void $onResponse
     * @return $this
     */
    public function debugResponse(callable $onResponse): static
    {
        $this->middleware()->onResponse(
            callable: static function (Response $response) use ($onResponse): void {
                $onResponse($response, $response->getPsrResponse());
            },
            order: PipeOrder::first()
        );

        return $this;
    }
}

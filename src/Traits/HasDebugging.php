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
     * Leave blank for a default debugger (requires symfony/var-dump)
     *
     * @param callable(\Saloon\Http\PendingRequest, \Psr\Http\Message\RequestInterface): void|null $onRequest
     * @return $this
     */
    public function debugRequest(?callable $onRequest = null, bool $die = false): static
    {
        if (is_null($onRequest)) {
            return debug($this, response: false, die: $die);
        }

        $this->middleware()->onRequest(
            callable: static function (PendingRequest $pendingRequest) use ($onRequest, $die): void {
                $onRequest($pendingRequest, $pendingRequest->createPsrRequest());

                if ($die) {
                    exit(1);
                }
            },
            order: PipeOrder::LAST
        );

        return $this;
    }

    /**
     * Register a response debugger
     *
     * Leave blank for a default debugger (requires symfony/var-dump)
     *
     * @param callable(\Saloon\Http\Response, \Psr\Http\Message\ResponseInterface): void|null $onResponse
     * @return $this
     */
    public function debugResponse(?callable $onResponse = null, bool $die = false): static
    {
        if (is_null($onResponse)) {
            return debug($this, request: false, die: $die);
        }

        $this->middleware()->onResponse(
            callable: static function (Response $response) use ($onResponse, $die): void {
                $onResponse($response, $response->getPsrResponse());

                if ($die) {
                    exit(1);
                }
            },
            order: PipeOrder::FIRST
        );

        return $this;
    }

    /**
     * Dump a request before it is sent and the response.
     *
     * Note that anything added by the sender will not be reflected here.
     *
     * Requires symfony/var-dumper
     */
    public function debug(bool $die = false): static
    {
        return debug($this, die: $die);
    }
}

<?php

declare(strict_types=1);

namespace Saloon\Traits;

use Saloon\Contracts\Connector;
use Saloon\Contracts\PendingRequest;
use Saloon\Contracts\Request;
use Saloon\Contracts\Response;
use Saloon\Debugging\DebugData;
use Saloon\Debugging\Debugger;

trait HasDebugging
{
    /**
     * @param callable(\Saloon\Debugging\Debugger): (void) $callback
     *
     * @return $this
     */
    public function debug(callable $callback): static
    {
        $middlewarePipeline = match (true) {
            $this instanceof Connector,
            $this instanceof Request => $this->middleware(),
        };

        $callback($debugger = new Debugger);

        $middlewarePipeline->onRequest(function (PendingRequest $pendingRequest) use ($debugger): void {
            $debugger->send(new DebugData($pendingRequest, null));
        })->onResponse(function (Response $response) use ($debugger): void {
            $debugger->send(new DebugData($response->getPendingRequest(), $response));
        });

        return $this;
    }
}

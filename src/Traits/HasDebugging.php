<?php

declare(strict_types=1);

namespace Saloon\Traits;

use Saloon\Contracts\Connector;
use Saloon\Contracts\PendingRequest;
use Saloon\Contracts\Request;
use Saloon\Contracts\Response;
use Saloon\Debugging\DebugData;
use Saloon\Debugging\Debugger;
use Saloon\Debugging\Drivers\RayDebugger;
use Saloon\Debugging\Drivers\SystemLogDebugger;

trait HasDebugging
{
    /**
     * @param callable(\Saloon\Debugging\Debugger): (void)|null $callback
     *
     * @return ($callback is null ? \Saloon\Debugging\Debugger : $this)
     */
    public function debug(?callable $callback = null): Debugger|static
    {
        $middlewarePipeline = match (true) {
            $this instanceof Connector,
            $this instanceof Request => $this->middleware(),
        };

        // Todo:
        // Come up with a nicer way to register these debuggers. Perhaps inside the
        // constructor of the debugger we can register them. It would be nice if we
        // can have a way to register additional like the telescope from the Laravel
        // package.

        // Todo: Make register driver static but allow people to register ad-hoc drivers

        $debugger = new Debugger;

        $debugger->registerDriver(new RayDebugger);
        $debugger->registerDriver(new SystemLogDebugger);

        if (! is_null($callback)) {
            $callback($debugger);
        }

        // Todo: Move this logic after the register default middleware so we log the very final PendingRequest.

        $middlewarePipeline->onRequest(function (PendingRequest $pendingRequest) use ($debugger): void {
            $debugger->send(new DebugData($pendingRequest, null));
        })->onResponse(function (Response $response) use ($debugger): void {
            $debugger->send(new DebugData($response->getPendingRequest(), $response));
        });

        return is_null($callback) ? $debugger : $this;
    }
}

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
use Saloon\Helpers\Helpers;

trait HasDebugging
{
    /**
     * Debugger
     *
     * @var \Saloon\Debugging\Debugger|null
     */
    protected ?Debugger $debugger = null;

    /**
     * Retrieve the debugger
     *
     * @param callable|null $callback
     * @return ($callback is null ? \Saloon\Debugging\Debugger : $this)
     */
    public function debug(?callable $callback = null): Debugger|static
    {
        $debugger = $this->debugger ??= new Debugger;

        if (is_null($callback)) {
            return $debugger;
        }

        return Helpers::tap($this, static fn () => $callback($debugger));
    }
}

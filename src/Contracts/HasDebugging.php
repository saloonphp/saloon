<?php

namespace Saloon\Contracts;

use Saloon\Debugging\Debugger;

interface HasDebugging
{
    /**
     * Retrieve the debugger
     *
     * @param callable|null $callback
     * @return ($callback is null ? \Saloon\Debugging\Debugger : $this)
     */
    public function debug(?callable $callback = null): Debugger|static;
}

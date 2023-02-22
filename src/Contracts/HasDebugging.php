<?php

declare(strict_types=1);

namespace Saloon\Contracts;

use Saloon\Debugging\Debugger;

interface HasDebugging
{
    /**
     * Retrieve the debugger
     *
     * @param callable|null $callback
     * @return \Saloon\Debugging\Debugger
     */
    public function debug(?callable $callback = null): Debugger;
}

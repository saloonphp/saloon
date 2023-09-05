<?php

declare(strict_types=1);

namespace Saloon\Contracts;

use Saloon\Debugging\Debugger;

/**
 * @internal
 */
interface HasDebugging
{
    /**
     * Retrieve the debugger
     *
     * @param callable(\Saloon\Debugging\Debugger): (void)|null $callback
     */
    public function debug(?callable $callback = null): Debugger;
}

<?php

declare(strict_types=1);

namespace Saloon\Traits;

use Saloon\Debugging\Debugger;

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
     * @param callable(\Saloon\Debugging\Debugger): (void)|null $callback
     * @return \Saloon\Debugging\Debugger
     */
    public function debug(?callable $callback = null): Debugger
    {
        $debugger = $this->debugger ??= new Debugger;

        if (is_callable($callback)) {
            $callback($debugger);
        }

        return $debugger;
    }
}

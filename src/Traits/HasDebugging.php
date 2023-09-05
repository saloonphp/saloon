<?php

declare(strict_types=1);

namespace Saloon\Traits;

use Saloon\Data\PipeOrder;

trait HasDebugging
{
    /**
     * Register a request debugger
     *
     * @return $this
     */
    public function debugRequest(callable $onRequest): static
    {
        $this->middleware()->onRequest($onRequest(...), order: PipeOrder::last());
    }

    /**
     * Register a response debugger
     *
     * @return $this
     */
    public function debugResponse(callable $onResponse): static
    {
        $this->middleware()->onResponse($onResponse(...), order: PipeOrder::first());
    }
}

<?php

declare(strict_types=1);

namespace Saloon\Contracts;

/**
 * @internal
 */
interface HasDebugging
{
    /**
     * Register a request debugger
     *
     * @return $this
     */
    public function debugRequest(callable $onRequest): static;

    /**
     * Register a response debugger
     *
     * @return $this
     */
    public function debugResponse(callable $onResponse): static;
}

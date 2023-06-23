<?php

declare(strict_types=1);

namespace Saloon\Traits;

use Saloon\Contracts\PendingRequest;
use Psr\Http\Message\RequestInterface;

trait HandlesPsrRequest
{
    /**
     * Handle the PSR request before it is sent
     *
     * @param \Psr\Http\Message\RequestInterface $request
     * @param \Saloon\Contracts\PendingRequest $pendingRequest
     * @return \Psr\Http\Message\RequestInterface
     */
    public function handlePsrRequest(RequestInterface $request, PendingRequest $pendingRequest): RequestInterface
    {
        return $request;
    }
}

<?php

declare(strict_types=1);

namespace Saloon\Traits;

use Saloon\Http\PendingRequest;
use Psr\Http\Message\RequestInterface;

trait HandlesPsrRequest
{
    /**
     * Handle the PSR request before it is sent
     */
    public function handlePsrRequest(RequestInterface $request, PendingRequest $pendingRequest): RequestInterface
    {
        return $request;
    }
}

<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Requests;

use Saloon\Http\PendingRequest;
use Psr\Http\Message\RequestInterface;

class ModifiedPsrUserRequest extends UserRequest
{
    public function handlePsrRequest(RequestInterface $request, PendingRequest $pendingRequest): RequestInterface
    {
        return $request->withHeader('X-Howdy', 'Yeehaw');
    }
}

<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Plugins;

use Saloon\Http\PendingRequest;

trait WithBootTestPlugin
{
    /**
     * Boot a test handler that adds a simple header to the response.
     *
     * @return void
     */
    public function bootWithBootTestPlugin(PendingRequest $pendingRequest)
    {
        $request = $pendingRequest->getRequest();

        $pendingRequest->headers()->add('X-Plugin-User-Id', $request->userId);
        $pendingRequest->headers()->add('X-Plugin-Group-Id', $request->groupId);
    }
}

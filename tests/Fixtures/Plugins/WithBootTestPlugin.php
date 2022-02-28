<?php

namespace Sammyjo20\Saloon\Tests\Fixtures\Plugins;

use Sammyjo20\Saloon\Http\SaloonRequest;

trait WithBootTestPlugin
{
    /**
     * Boot a test handler that adds a simple header to the response.
     *
     * @return void
     */
    public function bootWithBootTestPlugin(SaloonRequest $request)
    {
        $this->addHeader('X-Plugin-User-Id', $request->userId);
        $this->addHeader('X-Plugin-Group-Id', $request->groupId);
    }
}

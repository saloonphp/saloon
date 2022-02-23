<?php

namespace Sammyjo20\Saloon\Tests\Resources\Plugins;

use Psr\Http\Message\RequestInterface;
use Psr\Http\Message\ResponseInterface;
use Sammyjo20\Saloon\Http\SaloonRequest;
use Sammyjo20\Saloon\Http\SaloonConnector;

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

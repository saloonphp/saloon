<?php

namespace Saloon\Tests\Fixtures\Connectors;

use Saloon\Tests\Fixtures\Resources\UserBaseResource;

class ResourceConnector extends TestConnector
{
    public function user(): UserBaseResource
    {
        return new UserBaseResource($this);
    }
}

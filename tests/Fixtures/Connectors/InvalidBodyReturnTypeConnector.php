<?php

namespace Saloon\Tests\Fixtures\Connectors;

class InvalidBodyReturnTypeConnector extends TestConnector
{
    public function body(): bool
    {
        return false;
    }
}

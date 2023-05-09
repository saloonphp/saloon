<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Connectors;

class InvalidBodyReturnTypeConnector extends TestConnector
{
    public function body(): bool
    {
        return false;
    }
}

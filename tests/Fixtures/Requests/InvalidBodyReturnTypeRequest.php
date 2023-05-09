<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Requests;

class InvalidBodyReturnTypeRequest extends UserRequest
{
    public function body(): bool
    {
        return false;
    }
}

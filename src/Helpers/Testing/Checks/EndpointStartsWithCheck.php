<?php

declare(strict_types=1);

namespace Saloon\Helpers\Testing\Checks;

use Saloon\Traits\Makeable;
use Saloon\Http\PendingRequest;
use Saloon\Contracts\Testing\ValidatorCheck;

class EndpointStartsWithCheck implements ValidatorCheck
{
    use Makeable;

    public function __construct(protected PendingRequest $actual, protected string $expected)
    {

    }

    public function valid(): bool
    {
        return str_starts_with($this->actual->createPsrRequest()->getUri()->getPath(), $this->expected);
    }

    public function message(): string
    {
        return "The url did not start with '{$this->expected}'";
    }
}

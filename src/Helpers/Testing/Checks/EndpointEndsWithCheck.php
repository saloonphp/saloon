<?php

declare(strict_types=1);

namespace Saloon\Helpers\Testing\Checks;

use Saloon\Traits\Makeable;
use Saloon\Http\PendingRequest;
use Saloon\Contracts\Validators\ValidatorCheck;

class EndpointEndsWithCheck implements ValidatorCheck
{
    use Makeable;

    public function __construct(protected string $expected, protected PendingRequest $actual)
    {

    }

    public function valid(): bool
    {
        return str_ends_with($this->actual->createPsrRequest()->getUri()->getPath(), $this->expected);
    }

    public function message(): string
    {
        return "The url did not end with '{$this->expected}'";
    }
}

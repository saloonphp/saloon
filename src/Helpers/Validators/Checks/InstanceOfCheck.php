<?php

declare(strict_types=1);

namespace Saloon\Helpers\Validators\Checks;

use Saloon\Traits\Makeable;
use Saloon\Http\PendingRequest;
use Saloon\Contracts\Validators\ValidatorCheck;

class InstanceOfCheck implements ValidatorCheck
{
    use Makeable;

    public function __construct(protected string $expected, protected PendingRequest $actual)
    {

    }

    public function valid(): bool
    {
        return $this->actual->getRequest() instanceof $this->expected;
    }

    public function message(): string
    {
        return "The request is not an instance of {$this->expected}";
    }
}

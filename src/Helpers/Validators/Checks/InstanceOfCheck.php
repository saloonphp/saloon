<?php

declare(strict_types=1);

namespace Saloon\Helpers\Validators\Checks;

use Saloon\Http\Request;
use Saloon\Traits\Makeable;
use Saloon\Contracts\Validators\ValidatorCheck;

class InstanceOfCheck implements ValidatorCheck
{
    use Makeable;

    public function __construct(protected string $expected, protected Request $actual)
    {

    }

    public function valid(): bool
    {
        return $this->actual instanceof $this->expected;
    }

    public function message(): string
    {
        return "The request is not an instance of {$this->expected}";
    }
}

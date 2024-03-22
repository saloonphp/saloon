<?php

declare(strict_types=1);

namespace Saloon\Helpers\Testing\Checks;

use Closure;
use Saloon\Traits\Makeable;
use Saloon\Http\PendingRequest;
use Saloon\Contracts\Testing\ValidatorCheck;

class EndpointContainsCheck implements ValidatorCheck
{
    use Makeable;

    public function __construct(protected PendingRequest $actual, protected Closure|string $expected)
    {

    }

    public function valid(): bool
    {
        $path = $this->actual->createPsrRequest()->getUri()->getPath();

        if ($this->expected instanceof Closure) {
            return ($this->expected)($path);
        }

        return str_contains($path, $this->expected);
    }

    public function message(): string
    {
        return "The url did not contain '{$this->expected}'";
    }
}

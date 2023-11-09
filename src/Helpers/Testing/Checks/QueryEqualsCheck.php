<?php

declare(strict_types=1);

namespace Saloon\Helpers\Testing\Checks;

use Closure;
use Illuminate\Support\Arr;
use Saloon\Traits\Makeable;
use Saloon\Http\PendingRequest;
use Saloon\Contracts\Validators\ValidatorCheck;

class QueryEqualsCheck implements ValidatorCheck
{
    use Makeable;

    public function __construct(protected PendingRequest $actual, protected string| Closure $path, protected mixed $expected = null)
    {
    }

    public function valid(): bool
    {
        $query = $this->actual->query();

        if ($this->path instanceof Closure) {
            return ($this->path)($query);
        }

        return Arr::get($query->all(), $this->path) === $this->expected;
    }

    public function message(): string
    {
        return 'The query parameters did not contain the expected value';
    }
}

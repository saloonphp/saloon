<?php

declare(strict_types=1);

namespace Saloon\Helpers\Testing\Checks;

use Closure;
use Illuminate\Support\Arr;
use Saloon\Traits\Makeable;
use Saloon\Http\PendingRequest;
use Saloon\Contracts\Validators\ValidatorCheck;

class BodyEqualsCheck implements ValidatorCheck
{
    use Makeable;

    public function __construct(protected PendingRequest $actual, protected string| Closure $path, protected mixed $expected = null)
    {
    }

    public function valid(): bool
    {
        if ($this->path instanceof Closure) {
            return ($this->path)($this->actual);
        }

        $body = json_decode((string) $this->actual->body(), true);

        return Arr::get($body, $this->path) === $this->expected;
    }

    public function message(): string
    {
        return 'The body did not contain the expected value';
    }
}

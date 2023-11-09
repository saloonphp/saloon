<?php

declare(strict_types=1);

namespace Saloon\Helpers\Testing;

use Closure;
use Saloon\Http\PendingRequest;
use Saloon\Contracts\Testing\ValidatorCheck;
use Saloon\Helpers\Testing\Checks\BodyEqualsCheck;
use Saloon\Helpers\Testing\Checks\InstanceOfCheck;
use Saloon\Helpers\Testing\Checks\QueryEqualsCheck;
use Saloon\Helpers\Testing\Checks\EndpointContainsCheck;
use Saloon\Helpers\Testing\Checks\EndpointEndsWithCheck;
use Saloon\Helpers\Testing\Checks\EndpointStartsWithCheck;

class RequestValidator
{

    /**
     * @var array<ValidatorCheck>
     */
    protected array $checks = [];

    public function __construct(protected PendingRequest $request)
    {

    }

    public static function for(PendingRequest $request): self
    {
        return new static($request);
    }

    public function instanceOf(string $expected): self
    {
        $check = InstanceOfCheck::make(
            $this->request,
            $expected
        );

        $this->checks[] = $check;

        return $this;
    }

    public function endpointStartsWith(string $expected): self
    {
        $check = EndpointStartsWithCheck::make(
            $this->request,
            $expected
        );

        $this->checks[] = $check;

        return $this;
    }

    public function endpointEndsWith(string $expected): self
    {
        $check = EndpointEndsWithCheck::make(
            $this->request,
            $expected
        );

        $this->checks[] = $check;

        return $this;
    }

    public function endpointContains(Closure|string $expected): self
    {
        $check = EndpointContainsCheck::make(
            $this->request,
            $expected
        );

        $this->checks[] = $check;

        return $this;
    }

    public function bodyEquals(string|Closure $path, mixed $expected = null): self
    {
        $check = BodyEqualsCheck::make(
            $this->request,
            $path,
            $expected
        );

        $this->checks[] = $check;

        return $this;
    }

    public function queryEquals(string|Closure $path, mixed $expected = null): self
    {
        $check = QueryEqualsCheck::make(
            $this->request,
            $path,
            $expected
        );

        $this->checks[] = $check;

        return $this;
    }

    public function validate(): bool
    {
        return collect($this->checks)
            ->every(fn (ValidatorCheck $check) => $check->valid());
    }

    public function errors(): array
    {
        return collect($this->checks)
            ->filter(fn (ValidatorCheck $check) => ! $check->valid())
            ->map(fn (ValidatorCheck $check) => $check->message())
            ->toArray();
    }

}

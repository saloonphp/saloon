<?php

declare(strict_types=1);

namespace Saloon\Helpers\Testing;

use Saloon\Http\PendingRequest;
use Saloon\Contracts\Validators\ValidatorCheck;
use Saloon\Helpers\Testing\Checks\InstanceOfCheck;
use Saloon\Helpers\Testing\Checks\EndpointEndsWithCheck;

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

    public function instanceOf(string $class): self
    {
        $check = InstanceOfCheck::make(
            $class,
            $this->request
        );

        $this->checks[] = $check;

        return $this;
    }

    public function endpointEndsWith(string $url): self
    {
        $check = EndpointEndsWithCheck::make(
            $url,
            $this->request
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

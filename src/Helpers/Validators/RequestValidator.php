<?php

declare(strict_types=1);

namespace Saloon\Helpers\Validators;

use Saloon\Http\Request;
use Saloon\Http\PendingRequest;
use Saloon\Contracts\Validators\ValidatorCheck;
use Saloon\Helpers\Validators\Checks\InstanceOfCheck;

class RequestValidator
{

    /**
     * @var array<ValidatorCheck>
     */
    protected array $checks = [];

    public function __construct(protected Request $request)
    {

    }

    public static function for(Request $request): self
    {
        return new static($request);
    }

    public static function forPendingRequest(PendingRequest $request): self
    {
        return new static($request->getRequest());
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

    public function validate(): bool
    {
        return collect($this->checks)->every(fn (ValidatorCheck $check) => $check->valid());
    }

    public function errors(): array
    {
        return collect($this->checks)
            ->filter(fn (ValidatorCheck $check) => $check->valid())
            ->map(fn (ValidatorCheck $check) => $check->message())
            ->toArray();
    }

}

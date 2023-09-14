<?php

declare(strict_types=1);

namespace Saloon\Contracts\Validators;

interface ValidatorCheck
{

    public function valid(): bool;

    public function message(): string;

}

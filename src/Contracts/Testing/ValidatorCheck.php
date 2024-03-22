<?php

declare(strict_types=1);

namespace Saloon\Contracts\Testing;

interface ValidatorCheck
{

    public function valid(): bool;

    public function message(): string;

}

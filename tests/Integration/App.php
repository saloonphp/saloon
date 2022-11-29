<?php

declare(strict_types=1);

namespace Saloon\Tests\Integration;

class App
{
    public function __construct(
        public readonly int $appid,
        public readonly ?string $name,
    ) {
    }
}

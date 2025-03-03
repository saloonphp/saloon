<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Data;

use Saloon\Http\Response;
use Saloon\Contracts\DataObjects\IntoObjects;

class Superhero implements IntoObjects
{
    public function __construct(
        public string $name,
    ) {
        //
    }

    public static function fromResponse(Response $response): array
    {
        return $response->collect('data')
            ->map(function (array $item): self {
                return new static($item['superhero']);
            })
            ->toArray();
    }
}

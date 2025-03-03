<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Data;

use Saloon\Http\Response;
use Saloon\Traits\Responses\HasResponse;
use Saloon\Contracts\DataObjects\IntoObjects;
use Saloon\Contracts\DataObjects\WithResponse;

class SuperheroWithResponse implements IntoObjects, WithResponse
{
    use HasResponse;

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

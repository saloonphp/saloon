<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Data;

use Saloon\Contracts\DataObjects\DataTransferObject;
use Saloon\Contracts\DataObjects\WithResponse;
use Saloon\Http\Response;
use Saloon\Traits\Responses\HasResponse;

class IntoUserWithResponse implements DataTransferObject, WithResponse
{
    use HasResponse;

    public function __construct(
        public string $name,
        public string $actualName,
        public string $twitter,
    ) {
        //
    }

    public static function fromResponse(Response $response): static
    {
        $data = $response->json();

        return new static($data['name'], $data['actual_name'], $data['twitter']);
    }
}

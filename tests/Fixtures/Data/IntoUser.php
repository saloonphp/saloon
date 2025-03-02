<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Data;

use Saloon\Contracts\DataObjects\DataTransferObject;
use Saloon\Http\Response;

class IntoUser implements DataTransferObject
{
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

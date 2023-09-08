<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Data;

use Saloon\Http\Response;

class ApiResponse
{

    public function __construct(
        public array $data,
    ) {
        //
    }

    /**
     * @return static
     */
    public static function fromSaloon(Response $response): self
    {
        return new static($response->json());
    }
}

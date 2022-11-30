<?php

declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Data;

use Saloon\Contracts\Response;
use Saloon\Traits\Responses\HasResponse;
use Saloon\Contracts\DataObjects\WithResponse;

class UserWithResponse implements WithResponse
{
    use HasResponse;

    /**
     * @param string $name
     * @param string $actualName
     * @param string $twitter
     */
    public function __construct(
        public string $name,
        public string $actualName,
        public string $twitter,
    ) {
        //
    }

    /**
     * @param Response $response
     * @return static
     */
    public static function fromResponse(Response $response): static
    {
        $data = $response->json();

        return new static($data['name'], $data['actual_name'], $data['twitter']);
    }
}

<?php declare(strict_types=1);

namespace Saloon\Tests\Fixtures\Data;

use Saloon\Http\Responses\Response;

class ApiResponse
{
    /**
     * @param array $data
     */
    public function __construct(
        public array $data,
    ) {
        //
    }

    /**
     * @param Response $response
     * @return static
     */
    public static function fromSaloon(Response $response): self
    {
        return new static($response->json());
    }
}

<?php declare(strict_types=1);

namespace Saloon\Traits\Request;

use Saloon\Contracts\Response;

trait CastDtoFromResponse
{
    /**
     * Cast the response to a DTO.
     *
     * @param Response $response
     * @return mixed
     */
    public function createDtoFromResponse(Response $response): mixed
    {
        return null;
    }
}

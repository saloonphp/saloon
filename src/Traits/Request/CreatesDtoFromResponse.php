<?php

declare(strict_types=1);

namespace Saloon\Traits\Request;

use Saloon\Http\Response;

/**
 * @template TDto
 */
trait CreatesDtoFromResponse
{
    /**
     * Cast the response to a DTO.
     *
     * @param Response<mixed> $response
     * @return TDto|null
     */
    public function createDtoFromResponse(Response $response): mixed
    {
        return null;
    }
}

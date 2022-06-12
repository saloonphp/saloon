<?php

namespace Sammyjo20\Saloon\Traits;

use Sammyjo20\Saloon\Http\Responses\SaloonResponse;

trait CastsResponseToDto
{
    /**
     * Cast the response to a DTO.
     *
     * @param SaloonResponse $response
     * @return mixed
     */
    public function createDtoFromResponse(SaloonResponse $response): mixed
    {
        return null;
    }
}

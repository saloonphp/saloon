<?php

declare(strict_types=1);

namespace Saloon\Traits\Responses;

use Saloon\Http\Response;

trait HasResponse
{
    /**
     * The original response.
     */
    protected Response $response;

    /**
     * Set the response on the data object.
     *
     * @return $this
     */
    public function setResponse(Response $response): static
    {
        $this->response = $response;

        return $this;
    }

    /**
     * Get the response on the data object.
     */
    public function getResponse(): Response
    {
        return $this->response;
    }
}

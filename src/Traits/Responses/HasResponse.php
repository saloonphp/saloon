<?php

declare(strict_types=1);

namespace Saloon\Traits\Responses;

use Saloon\Contracts\Response;

trait HasResponse
{
    /**
     * The original response.
     *
     * @var \Saloon\Contracts\Response
     */
    protected Response $response;

    /**
     * Set the response on the data object.
     *
     * @param \Saloon\Contracts\Response $response
     * @return $this
     */
    public function setResponse(Response $response): static
    {
        $this->response = $response;

        return $this;
    }

    /**
     * Get the response on the data object.
     *
     * @return \Saloon\Contracts\Response
     */
    public function getResponse(): Response
    {
        return $this->response;
    }
}

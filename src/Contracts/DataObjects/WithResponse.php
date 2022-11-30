<?php

declare(strict_types=1);

namespace Saloon\Contracts\DataObjects;

use Saloon\Contracts\Response;

interface WithResponse
{
    /**
     * Set the response on the data object.
     *
     * @param \Saloon\Contracts\Response $response
     * @return $this
     */
    public function setResponse(Response $response): static;

    /**
     * Get the response on the data object.
     *
     * @return \Saloon\Contracts\Response
     */
    public function getResponse(): Response;
}

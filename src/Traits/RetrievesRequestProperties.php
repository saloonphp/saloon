<?php

namespace Sammyjo20\Saloon\Traits;

use Sammyjo20\Saloon\Data\RequestProperties;

trait RetrievesRequestProperties
{
    /**
     * Get all the request properties with their default set.
     *
     * @return RequestProperties
     */
    public function getRequestProperties(): RequestProperties
    {
        return new RequestProperties(
            $this->headers()->all(),
            $this->queryParameters()->all(),
            $this->data()->all(),
            $this->config()->all(),
            $this->middleware(),
        );
    }
}

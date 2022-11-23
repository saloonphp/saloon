<?php

declare(strict_types=1);

namespace Saloon\Traits\Request;

use Saloon\Helpers\URLHelper;
use Saloon\Exceptions\InvalidConnectorException;

trait BuildsUrls
{
    /**
     * Build up the full request URL.
     *
     * @return string
     * @throws InvalidConnectorException
     */
    public function getRequestUrl(): string
    {
        return URLHelper::join($this->connector()->defineBaseUrl(), $this->defineEndpoint());
    }
}

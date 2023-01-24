<?php

declare(strict_types=1);

namespace Saloon\Traits\RequestProperties;

use Saloon\Repositories\ArrayStore;
use Saloon\Contracts\ArrayStore as ArrayStoreContract;

trait HasHeaders
{
    /**
     * Request Headers
     *
     * @var \Saloon\Contracts\ArrayStore
     */
    protected ArrayStoreContract $headers;

    /**
     * Access the headers
     *
     * @return \Saloon\Contracts\ArrayStore
     */
    public function headers(): ArrayStoreContract
    {
        return $this->headers ??= new ArrayStore($this->defaultHeaders());
    }

    /**
     * Default Request Headers
     *
     * @return array
     */
    protected function defaultHeaders(): array
    {
        return [];
    }
}

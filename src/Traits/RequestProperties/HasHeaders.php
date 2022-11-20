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
     * @var ArrayStoreContract
     */
    protected ArrayStoreContract $headers;

    /**
     * Access the headers
     *
     * @return ArrayStoreContract
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

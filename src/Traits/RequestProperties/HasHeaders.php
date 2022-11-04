<?php declare(strict_types=1);

namespace Sammyjo20\Saloon\Traits\RequestProperties;

use Sammyjo20\Saloon\Repositories\ArrayStore;
use Sammyjo20\Saloon\Contracts\ArrayStore as ArrayStoreContract;

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

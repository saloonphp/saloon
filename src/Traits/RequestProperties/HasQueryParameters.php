<?php declare(strict_types=1);

namespace Sammyjo20\Saloon\Traits\RequestProperties;

use Sammyjo20\Saloon\Repositories\ArrayStore;
use Sammyjo20\Saloon\Contracts\ArrayStore as ArrayStoreContract;

trait HasQueryParameters
{
    /**
     * Request Query Parameters
     *
     * @var ArrayStoreContract
     */
    protected ArrayStoreContract $queryParameters;

    /**
     * Access the query parameters
     *
     * @return ArrayStoreContract
     */
    public function queryParameters(): ArrayStoreContract
    {
        return $this->queryParameters ??= new ArrayStore($this->defaultQueryParameters());
    }

    /**
     * Default Query Parameters
     *
     * @return array
     */
    protected function defaultQueryParameters(): array
    {
        return [];
    }
}

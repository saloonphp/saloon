<?php

declare(strict_types=1);

namespace Saloon\Traits\RequestProperties;

use Saloon\Repositories\ArrayStore;
use Saloon\Contracts\ArrayStore as ArrayStoreContract;

trait HasQuery
{
    /**
     * Request Query Parameters
     *
     * @var ArrayStoreContract
     */
    protected ArrayStoreContract $query;

    /**
     * Access the query parameters
     *
     * @return ArrayStoreContract
     */
    public function query(): ArrayStoreContract
    {
        return $this->query ??= new ArrayStore($this->defaultQuery());
    }

    /**
     * Default Query Parameters
     *
     * @return array
     */
    protected function defaultQuery(): array
    {
        return [];
    }
}
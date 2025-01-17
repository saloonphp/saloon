<?php

declare(strict_types=1);

namespace Saloon\Traits\RequestProperties;

use Saloon\Repositories\ArrayStore;
use Saloon\Contracts\ArrayStore as ArrayStoreContract;

trait HasQuery
{
    /**
     * Request Query Parameters
     */
    protected ArrayStoreContract $query;

    /**
     * Access the query parameters
     */
    public function query(): ArrayStoreContract
    {
        return $this->query ??= $this->resolveQueryBuilder()->set($this->defaultQuery());
    }

    /**
     * Define the query builder class
     */
    protected function resolveQueryBuilder(): ArrayStoreContract
    {
        return new ArrayStore();
    }

    /**
     * Default Query Parameters
     *
     * @return array<string, mixed>
     */
    protected function defaultQuery(): array
    {
        return [];
    }
}

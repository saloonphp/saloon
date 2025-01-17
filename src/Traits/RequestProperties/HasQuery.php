<?php

declare(strict_types=1);

namespace Saloon\Traits\RequestProperties;

use Closure;
use Saloon\Contracts\ArrayStore as ArrayStoreContract;
use Saloon\Repositories\ArrayStore;

trait HasQuery
{
    /**
     * Specify the default query builder class
     */
    protected string $queryBuilder = ArrayStore::class;

    /**
     * Request Query Parameters
     */
    protected ArrayStoreContract $query;

    /**
     * Access the query parameters
     */
    public function query(): ArrayStoreContract
    {
        return $this->query ??= $this->resolveQueryBuilder();
    }

    /**
     * Access the query parameters fluently
     */
    public function fluentQuery(?Closure $callback = null): self
    {
        $callback($this->query());

        return $this;
    }

    /**
     * Define the query builder class
     */
    protected function resolveQueryBuilder(): ArrayStoreContract
    {
        return new $this->queryBuilder($this->defaultQuery());
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

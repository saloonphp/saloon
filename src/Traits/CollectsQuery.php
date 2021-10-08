<?php

namespace Sammyjo20\Saloon\Traits;

trait CollectsQuery
{
    protected array $query = [];

    public function defineQuery(): array
    {
        return [];
    }

    public function setQuery(array $query): self
    {
        $this->query = $query;

        return $this;
    }

    public function addQuery(string $param, string $value): self
    {
        $this->query[$param] = $value;

        return $this;
    }

    public function mergeQuery(array $params): self
    {
        $this->query = array_merge($this->query, $params);

        return $this;
    }

    public function getQuery(): array
    {
        return $this->query;
    }

    public function query(string $param): mixed
    {
        return $this->query[$param];
    }
}

<?php

namespace Sammyjo20\Saloon\Traits;

use Illuminate\Support\Arr;
use Sammyjo20\Saloon\Http\SaloonRequest;

trait CollectsQueryParams
{
    /**
     * Query that have been added, this doesn't include default query.
     *
     * @var array
     */
    private array $customQuery = [];

    /**
     * Should we include the default query when using ->getQuery()?
     *
     * @var bool
     */
    public bool $includeDefaultQuery = true;

    /**
     * Default query.
     *
     * @return array
     */
    public function defaultQuery(): array
    {
        return [];
    }

    /**
     * Merge query together into one array.
     *
     * @param mixed ...$queryCollection
     * @return $this
     */
    public function mergeQuery(array ...$queryCollection): static
    {
        foreach ($queryCollection as $query) {
            $this->customQuery = array_merge($this->customQuery, $query);
        }

        return $this;
    }

    /**
     * Set the whole query array.
     *
     * @param array $query
     * @return $this
     */
    public function setQuery(array $query): static
    {
        $this->ignoreDefaultQuery();

        $this->customQuery = $query;

        return $this;
    }

    /**
     * Add an individual query param.
     *
     * @param string $query
     * @param $value
     * @return $this
     */
    public function addQuery(string $query, $value): static
    {
        $this->customQuery[$query] = $value;

        return $this;
    }

    /**
     *  Get all query or filter with a key.
     *
     * @param string|null $key
     * @return mixed
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonInvalidConnectorException
     */
    public function getQuery(string $key = null): mixed
    {
        if ($this->includeDefaultQuery === true) {
            // Let's merge in the query parameters from the connector if
            // the connector has the trait.

            $queryBag = $this instanceof SaloonRequest ? $this->getConnector()->getQuery() : [];

            // Now let's merge the request query parameters because they take priority

            $queryBag = array_merge($queryBag, $this->defaultQuery(), $this->customQuery);
        } else {
            $queryBag = $this->customQuery;
        }

        if (isset($key)) {
            return Arr::get($queryBag, $key);
        }

        return $queryBag;
    }

    /**
     * Should we ignore the default query when calling `->getQuery()`?
     *
     * @return $this
     */
    public function ignoreDefaultQuery(): static
    {
        $this->includeDefaultQuery = false;

        return $this;
    }
}

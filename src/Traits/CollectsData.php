<?php

namespace Sammyjo20\Saloon\Traits;

use Illuminate\Support\Arr;
use Sammyjo20\Saloon\Http\SaloonRequest;

trait CollectsData
{
    /**
     * Data that have been added, this doesn't include default data.
     *
     * @var array
     */
    private array $customData = [];

    /**
     * Should we include the default data when using ->getData()?
     *
     * @var bool
     */
    public bool $includeDefaultData = true;

    /**
     * Default data.
     *
     * @return array
     */
    public function defaultData(): array
    {
        return [];
    }

    /**
     * Merge data together into one array.
     *
     * @param mixed ...$dataCollection
     * @return $this
     */
    public function mergeData(array ...$dataCollection): static
    {
        foreach ($dataCollection as $data) {
            $this->customData = array_merge($this->customData, $data);
        }

        return $this;
    }

    /**
     * Set the whole data array.
     *
     * @param array $data
     * @return $this
     */
    public function setData(array $data): static
    {
        $this->ignoreDefaultData();

        $this->customData = $data;

        return $this;
    }

    /**
     * Add an individual data.
     *
     * @param string $data
     * @param $value
     * @return $this
     */
    public function addData(string $data, $value): static
    {
        $this->customData[$data] = $value;

        return $this;
    }

    /**
     * Get all data or filter with a key.
     *
     * @param string|null $key
     * @return mixed
     * @throws \Sammyjo20\Saloon\Exceptions\SaloonInvalidConnectorException
     */
    public function getData(string $key = null): mixed
    {
        if ($this->includeDefaultData === true) {
            // Let's merge in the query parameters from the connector if
            // the connector has the trait.

            if ($this instanceof SaloonRequest && method_exists($this, 'connectorHasDataTrait') && $this->connectorHasDataTrait()) {
                $dataBag = $this->getConnector()->getData();
            } else {
                $dataBag = [];
            }

            // Now let's merge the request query parameters because they take priority

            $dataBag = array_merge($dataBag, $this->defaultData(), $this->customData);
        } else {
            $dataBag = $this->customData;
        }

        if (isset($key)) {
            return Arr::get($dataBag, $key);
        }

        return $dataBag;
    }

    /**
     * Should we ignore the default data when calling `->getData()`?
     *
     * @return $this
     */
    public function ignoreDefaultData(): static
    {
        $this->includeDefaultData = false;

        return $this;
    }
}

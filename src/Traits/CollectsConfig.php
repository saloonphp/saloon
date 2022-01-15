<?php

namespace Sammyjo20\Saloon\Traits;

use Illuminate\Support\Arr;

trait CollectsConfig
{
    /**
     * Config that have been added. This doesn't include default headers.
     *
     * @var array
     */
    private array $customConfig = [];

    /**
     * Should we include the default config using ->getConfig()?
     *
     * @var bool
     */
    private bool $includeDefaultConfig = true;

    /**
     * Default config variables.
     *
     * @return array
     */
    public function defaultConfig(): array
    {
        return [];
    }

    /**
     * Merge all the config into one array.
     *
     * @param mixed ...$configCollection
     * @return $this
     */
    public function mergeConfig(array ...$configCollection): self
    {
        foreach ($configCollection as $config) {
            $this->customConfig = array_merge($this->customConfig, $config);
        }

        return $this;
    }

    /**
     * Set the whole config array.
     * If you call this, we will ignore all default config.
     *
     * @param array $config
     * @return $this
     */
    public function setConfig(array $config): self
    {
        $this->ignoreDefaultConfig();

        $this->customConfig = $config;

        return $this;
    }

    /**
     * Add an individual config variable.
     *
     * @param string $item
     * @param $value
     * @return $this
     */
    public function addConfigVariable(string $item, $value): self
    {
        $this->customConfig[$item] = $value;

        return $this;
    }

    /**
     * Get all headers or filter with a key.
     * Todo: Throw an error if it doesn't exist.
     *
     * @param string|null $key
     * @return array
     */
    public function getConfig(string $key = null): array
    {
        if ($this->includeDefaultConfig === true) {
            $configBag = array_merge($this->defaultConfig(), $this->customConfig);
        } else {
            $configBag = $this->customConfig;
        }

        if (isset($key)) {
            return Arr::get($configBag, $key);
        }

        return $configBag;
    }

    /**
     * Should we ignore the default config when calling `->getConfig()`?
     *
     * @return $this
     */
    public function ignoreDefaultConfig(): self
    {
        $this->includeDefaultConfig = false;

        return $this;
    }
}

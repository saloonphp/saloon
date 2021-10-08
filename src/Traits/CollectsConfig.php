<?php

namespace Sammyjo20\Saloon\Traits;

trait CollectsConfig
{
    protected array $config = [];

    public function defineConfig(): array
    {
        return [];
    }

    public function setConfig(array $config): self
    {
        $this->config = $config;

        return $this;
    }

    public function addConfig(string $option, $value): self
    {
        $this->config[$option] = $value;

        return $this;
    }

    public function mergeConfig(array $config): self
    {
        $this->config = array_merge($this->config, $config);

        return $this;
    }

    public function getConfig(): array
    {
        return $this->config;
    }

    public function config(string $option): mixed
    {
        return $this->config[$option];
    }
}

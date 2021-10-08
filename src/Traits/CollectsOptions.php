<?php

namespace Sammyjo20\Saloon\Traits;

trait CollectsOptions
{
    protected array $options = [];

    public function defineOptions(): array
    {
        return [];
    }

    public function setOptions(array $options): self
    {
        $this->options = $options;

        return $this;
    }

    public function addOption(string $option, $value): self
    {
        $this->options[$option] = $value;

        return $this;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function option(string $option): mixed
    {
        return $this->options[$option];
    }
}

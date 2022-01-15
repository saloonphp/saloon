<?php

namespace Sammyjo20\Saloon\Traits;

trait CollectsData
{
    protected array $data = [];

    protected bool $overwriteDefaults = false;

    public function setData(array $data): self
    {
        $this->overwriteDefaults = true;
        $this->data = $data;

        return $this;
    }

    public function addData(string $item, $value): self
    {
        $this->data[$item] = $value;

        return $this;
    }

    /**
     * @return array
     */
    public function postData(): array
    {
        return [];
    }

    /**
     * Get all data, if setData has been used, don't include the defaults.
     *
     * @return array
     */
    public function allData(): array
    {
        if ($this->overwriteDefaults === true) {
            return $this->data;
        }

        return array_merge($this->data, $this->postData());
    }

    public function data(string $item): mixed
    {
        return $this->data[$item];
    }

    public function shouldOverwriteDefaults(): bool
    {
        return $this->overwriteDefaults;
    }
}

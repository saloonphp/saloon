<?php

namespace Sammyjo20\Saloon\Traits;

trait CollectsData
{
    protected array $data = [];

    public function defineData(): array
    {
        return [];
    }

    public function setData(array $data): self
    {
        $this->data = $data;

        return $this;
    }

    public function addData(string $item, $value): self
    {
        $this->data[$item] = $value;

        return $this;
    }

    public function getData(): array
    {
        return $this->data;
    }

    public function data(string $item): mixed
    {
        return $this->data[$item];
    }
}

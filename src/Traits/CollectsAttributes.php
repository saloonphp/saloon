<?php

namespace Sammyjo20\Saloon\Traits;

trait CollectsAttributes
{
    protected array $requestAttributes = [];

    public function setRequestAttributes(array $attributes): self
    {
        $this->requestAttributes = $attributes;

        return $this;
    }

    public function getRequestAttributes(): array
    {
        return $this->requestAttributes;
    }

    public function getAttribute(string $attribute): mixed
    {
        return $this->requestAttributes[$attribute];
    }
}

<?php

namespace Sammyjo20\Saloon\Traits;

use Illuminate\Support\Arr;
use Sammyjo20\Saloon\Exceptions\SaloonMissingAttributeException;

trait CollectsAttributes
{
    /**
     * Attributes passed into the constructor of the request.
     *
     * @var array
     */
    protected array $requestAttributes = [];

    /**
     * Set the request attributes.
     *
     * @param array $attributes
     * @return $this
     */
    public function setRequestAttributes(array $attributes): self
    {
        $this->requestAttributes = $attributes;

        return $this;
    }

    /**
     * Get all the attributes.
     *
     * @return array
     */
    public function getAttributes(): array
    {
        return $this->requestAttributes;
    }

    /**
     * Get an individual attribute.
     *
     * @param string $attribute
     * @return mixed
     * @throws SaloonMissingAttributeException
     */
    public function getAttribute(string $attribute): mixed
    {
        if (! isset($this->requestAttributes[$attribute])) {
            throw new SaloonMissingAttributeException($this, $attribute);
        }

        return Arr::get($this->requestAttributes, $attribute);
    }
}

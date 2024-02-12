<?php

declare(strict_types=1);

namespace Saloon\Repositories\Body;

use Stringable;
use Saloon\Traits\Body\CreatesStreamFromString;

class JsonBodyRepository extends ArrayBodyRepository implements Stringable
{
    use CreatesStreamFromString;

    /**
     * JSON encoding flags
     *
     * Use a Bitmask to separate other flags. For example: JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR
     */
    protected int $jsonFlags = JSON_THROW_ON_ERROR;

    /**
     * Set the JSON encoding flags
     *
     * Must be a bitmask like: ->setJsonFlags(JSON_UNESCAPED_SLASHES | JSON_THROW_ON_ERROR)
     *
     * @return $this
     */
    public function setJsonFlags(int $flags): static
    {
        $this->jsonFlags = $flags;

        return $this;
    }

    /**
     * Get the JSON encoding flags
     */
    public function getJsonFlags(): int
    {
        return $this->jsonFlags;
    }

    /**
     * Convert the body repository into a string.
     */
    public function __toString(): string
    {
        $json = json_encode($this->all(), $this->getJsonFlags());

        return $json === false ? '' : $json;
    }
}

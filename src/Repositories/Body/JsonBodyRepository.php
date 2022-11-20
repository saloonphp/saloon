<?php

declare(strict_types=1);

namespace Saloon\Repositories\Body;

class JsonBodyRepository extends ArrayBodyRepository
{
    /**
     * Convert the body repository into a string.
     *
     * @return string
     * @throws \JsonException
     */
    public function __toString(): string
    {
        return json_encode($this->all(), JSON_THROW_ON_ERROR);
    }
}

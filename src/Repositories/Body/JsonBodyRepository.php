<?php

declare(strict_types=1);

namespace Saloon\Repositories\Body;

use Saloon\Traits\Body\CreatesStreamFromString;

class JsonBodyRepository extends ArrayBodyRepository
{
    use CreatesStreamFromString;

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

<?php

declare(strict_types=1);

namespace Saloon\Repositories\Body;

class FormBodyRepository extends ArrayBodyRepository
{
    /**
     * Convert into a string.
     *
     * @return string
     */
    public function __toString(): string
    {
        return http_build_query($this->all());
    }
}

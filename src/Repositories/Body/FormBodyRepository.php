<?php

declare(strict_types=1);

namespace Saloon\Repositories\Body;

use Saloon\Traits\Body\CreatesStreamFromString;
use Stringable;

class FormBodyRepository extends ArrayBodyRepository implements Stringable
{
    use CreatesStreamFromString;

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

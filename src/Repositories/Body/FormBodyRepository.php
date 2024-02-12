<?php

declare(strict_types=1);

namespace Saloon\Repositories\Body;

use Stringable;
use Saloon\Traits\Body\CreatesStreamFromString;

class FormBodyRepository extends ArrayBodyRepository implements Stringable
{
    use CreatesStreamFromString;

    /**
     * Convert into a string.
     */
    public function __toString(): string
    {
        return http_build_query($this->all());
    }
}

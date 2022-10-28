<?php

namespace Sammyjo20\Saloon\Data;

enum DataBagType: string
{
    case ARRAY = 'array';
    case STRING = 'string';

    /**
     * Validate the data.
     *
     * @param mixed $data
     * @return bool
     */
    public function isCompatibleWith(array|string $data): bool
    {


        return match ($this) {
            self::ARRAY => is_array($data),
            default => ! is_array($data),
        };
    }
}

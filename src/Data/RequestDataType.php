<?php

namespace Sammyjo20\Saloon\Data;

enum RequestDataType: string
{
    case JSON = 'json';
    case FORM = 'form';
    case MULTIPART = 'multipart';
    case STRING = 'string';

    /**
     * Returns if the data type is defined as an array.
     *
     * @return bool
     */
    public function isArrayable(): bool
    {
        return in_array($this, [self::JSON, self::FORM, self::MULTIPART], true);
    }
}

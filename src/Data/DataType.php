<?php

namespace Sammyjo20\Saloon\Data;

enum DataType: string
{
    case MIXED = 'mixed';
    case FORM = 'form';
    case MULTIPART = 'multipart';
    case JSON = 'json';

    /**
     * @return bool
     */
    public function isArrayable(): bool
    {
        return $this !== self::MIXED;
    }
}

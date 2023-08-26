<?php

declare(strict_types=1);

namespace Saloon\Enums;

enum Order: string
{
    case FIRST = 'first';
    case LAST = 'last';
}
